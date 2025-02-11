<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScholarController extends Controller
{
    // URL พื้นฐานของ Google Scholar
    private $base_url = "https://scholar.google.com/citations";
    // จำนวนครั้งสูงสุดที่ retry เมื่อ request ล้มเหลว
    private $max_retries = 3;
    // เวลาเริ่มต้นสำหรับหน่วง (วินาที)
    private $initial_delay = 60;
    // Guzzle client
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * สุ่มเลือก User-Agent จากอาร์เรย์
     */
    private function getRandomUserAgent()
    {
        $userAgents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:88.0) Gecko/20100101 Firefox/88.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36"
        ];
        return $userAgents[array_rand($userAgents)];
    }

    /**
     * สร้าง header สำหรับ HTTP request
     */
    private function getHeaders()
    {
        return [
            'User-Agent' => $this->getRandomUserAgent(),
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Cache-Control' => 'max-age=0'
        ];
    }

    /**
     * ส่ง HTTP GET request พร้อมระบบ retry (หากถูกจำกัดหรือเกิดข้อผิดพลาด)
     */
    private function handleRequest($url, $params = [], $retryCount = 0)
    {
        if ($retryCount >= $this->max_retries) {
            return null;
        }
        try {
            // หน่วงเวลาแบบสุ่ม 2-5 วินาที เพื่อหลีกเลี่ยงการถูก block
            sleep(rand(2, 5));
            $options = [
                'headers' => $this->getHeaders(),
                'query'   => $params
            ];
            $response = $this->client->get($url, $options);
            if ($response->getStatusCode() == 429) {
                // หากถูกจำกัด (Too Many Requests) ให้หน่วงเวลาก่อน retry
                $retryDelay = $this->initial_delay * pow(2, $retryCount);
                sleep($retryDelay);
                return $this->handleRequest($url, $params, $retryCount + 1);
            }
            if ($response->getStatusCode() != 200) {
                return null;
            }
            return $response;
        } catch (\Exception $e) {
            $retryDelay = $this->initial_delay * pow(2, $retryCount);
            sleep($retryDelay);
            return $this->handleRequest($url, $params, $retryCount + 1);
        }
    }

    /**
     * ดึงข้อมูลโปรไฟล์นักวิจัยจากผลการค้นหาของ Google Scholar
     */
    public function getResearcherProfile($fullName)
    {
        $params = [
            'view_op'  => 'search_authors',
            'mauthors' => $fullName,
            'hl'       => 'en'
        ];
        $response = $this->handleRequest($this->base_url, $params);
        if (!$response) {
            return null;
        }
        $html = (string)$response->getBody();
        // บันทึก HTML สำหรับ debug
        file_put_contents(storage_path('logs/google_scholar_debug.html'), $html);

        $crawler = new Crawler($html);
        $profiles = $crawler->filter('div.gsc_1usr');
        if ($profiles->count() == 0) {
            return null;
        }
        // ใช้ profile แรกที่พบ
        $profileElement = $profiles->first()->getNode(0);
        $profileCrawler = new Crawler($profileElement);

        // ดึงชื่อโปรไฟล์และ URL
        if ($profileCrawler->filter('h3.gs_ai_name a')->count() == 0) {
            return null;
        }
        $researcherName = trim($profileCrawler->filter('h3.gs_ai_name a')->text());
        $profileHref = $profileCrawler->filter('h3.gs_ai_name a')->attr('href');
        $profileUrl  = "https://scholar.google.com" . $profileHref;

        // ดึงข้อมูลเพิ่มเติมจากโปรไฟล์
        $affiliation = $profileCrawler->filter('div.gs_ai_aff')->count()
            ? trim($profileCrawler->filter('div.gs_ai_aff')->text())
            : 'N/A';
        $email = $profileCrawler->filter('div.gs_ai_eml')->count()
            ? trim($profileCrawler->filter('div.gs_ai_eml')->text())
            : 'N/A';
        $citedText = $profileCrawler->filter('div.gs_ai_cby')->count()
            ? trim($profileCrawler->filter('div.gs_ai_cby')->text())
            : '0';
        preg_match('/Cited by (\d+)/i', $citedText, $matches);
        $cited_by = isset($matches[1]) ? (int)$matches[1] : 0;
        $interests = [];
        if ($profileCrawler->filter('div.gs_ai_int a')->count()) {
            $profileCrawler->filter('div.gs_ai_int a')->each(function (Crawler $node) use (&$interests) {
                $interests[] = trim($node->text());
            });
        }
        // ดึง user id จาก URL ของโปรไฟล์
        $parts = parse_url($profileUrl);
        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $userId = isset($query['user']) ? $query['user'] : '';

        // ดึงข้อมูลผลงาน (papers) พร้อมรายละเอียด paper-detail
        $papers = $this->getAllPapers($userId);

        return [
            'researcher_name' => $researcherName,
            'profile_url'     => $profileUrl,
            'profile'         => [
                'affiliation' => $affiliation,
                'email'       => $email,
                'cited_by'    => $cited_by,
                'interests'   => $interests
            ],
            'publications'    => $papers
        ];
    }

    /**
     * ดึงข้อมูลผลงานทั้งหมดจากหน้าโปรไฟล์ของนักวิจัย
     * สำหรับแต่ละ paper จะเข้าไปที่ paper_url เพื่อดึงรายละเอียดเพิ่มเติม
     */
    public function getAllPapers($userId)
    {
        $publications = [];
        $start = 0;
        while (true) {
            $url = "https://scholar.google.com/citations";
            $params = [
                'user'     => $userId,
                'hl'       => 'en',
                'cstart'   => $start,
                'pagesize' => 100
            ];
            $response = $this->handleRequest($url, $params);
            if (!$response) {
                break;
            }
            $html = (string)$response->getBody();
            $crawler = new Crawler($html);
            $papers = $crawler->filter('tr.gsc_a_tr');
            if ($papers->count() == 0) {
                break;
            }
            foreach ($papers as $paperElement) {
                $paperCrawler = new Crawler($paperElement);
                $pubData = [];

                // ดึงชื่อบทความและ URL ของ paper detail
                $titleTag = $paperCrawler->filter('a.gsc_a_at');
                if ($titleTag->count() == 0 || trim($titleTag->text()) == '') {
                    continue;
                }
                $pubData['title'] = trim($titleTag->text());
                $pubData['paper_url'] = "https://scholar.google.com" . $titleTag->attr('href');

                // ดึง authors (จาก div.gs_gray ส่วนแรก) แล้วแยกออกเป็นอาร์เรย์
                $authorsVenue = $paperCrawler->filter('div.gs_gray')->first();
                if ($authorsVenue->count()) {
                    $authorsText = trim($authorsVenue->text());
                    $authorsArray = array_map('trim', explode(',', $authorsText));
                    $pubData['authors'] = $authorsArray;
                } else {
                    $pubData['authors'] = ['N/A'];
                }

                // ดึง venue (ส่วนที่สองของ div.gs_gray)
                $venueNodes = $paperCrawler->filter('div.gs_gray');
                $pubData['venue'] = $venueNodes->count() > 1 ? trim($venueNodes->eq(1)->text()) : 'N/A';

                // ดึงปีที่ตีพิมพ์
                $yearNode = $paperCrawler->filter('span.gsc_a_h');
                $pubData['year'] = ($yearNode->count() && trim($yearNode->text()) != '') ? trim($yearNode->text()) : 'N/A';

                // ดึงจำนวนการอ้างอิง
                $citationsNode = $paperCrawler->filter('a.gsc_a_ac');
                $pubData['citations'] = ($citationsNode->count() && trim($citationsNode->text()) != '') ? trim($citationsNode->text()) : '0';

                // เข้าสู่หน้ารายละเอียดของ paper เพื่อดึงข้อมูลเพิ่มเติม
                $details = $this->getPaperDetails($titleTag->attr('href'));
                $pubData['details'] = $details ? $details : [];

                $publications[] = $pubData;
            }
            // ถ้าจำนวนแถวที่ดึงน้อยกว่า 100 แสดงว่าเป็นหน้าสุดท้าย
            if ($papers->count() < 100) {
                break;
            }
            $start += 100;
            sleep(rand(2, 5));
        }
        return $publications;
    }

    /**
     * ดึงรายละเอียดของ paper จาก paper_url โดยใช้ XPath
     *
     * พยายามดึงข้อมูลเช่น Authors, Publication date, Journal, Volume, Issue, Pages, Publisher, Description
     */
    private function getPaperDetails($paperUrl)
    {
        // ตรวจสอบว่า $paperUrl เป็น absolute URL หรือไม่
        if (strpos($paperUrl, 'http') !== 0) {
            // หาก $paperUrl เป็น relative URL ให้เติมโดเมนเข้าไป
            $paperUrl = "https://scholar.google.com" . $paperUrl;
        }
        $client = new Client([
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                    . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                    . 'Chrome/108.0.0.0 Safari/537.36',
            ],
        ]);
    
        try {
            // ส่ง HTTP GET request ไปยัง paper URL
            $response = $client->get($paperUrl);
            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูลจาก Google Scholar ได้: ' . $e->getMessage()
            ], 500);
        }
    
        // สร้าง instance ของ DomCrawler เพื่อวิเคราะห์ HTML
        $crawler = new Crawler($html);
    
        // ดึง title ของงานวิจัย (ถ้ามี)
        $paperTitle = '';
        try {
            // title จะอยู่ใน selector ที่เป็น .gsc_oci_title
            $paperTitle = $crawler->filter('.gsc_oci_title')->count() 
                ? $crawler->filter('.gsc_oci_title')->text() 
                : '';
        } catch (\Exception $e) {
            // หากหาไม่เจอก็ปล่อยว่าง
            $paperTitle = '';
        }
    
        /**
         * ในหน้า Google Scholar Citations details
         * แต่ละ field จะอยู่ใน <div class="gsc_oci_field"> แล้วค่าจะอยู่ใน <div class="gsc_oci_value">
         * เราสามารถวน loop ตามดึงค่าทั้งหมดได้
         */
        $details = [
            'title'             => trim($paperTitle),
            'authors'           => '',
            'publication_date'  => '',
            'journal'           => '',
            'volume'            => '',
            'issue'             => '',
            'pages'             => '',
            'publisher'         => '',
            'description'       => '',
        ];
    
        try {
            // เอาทุก field-value มา match กันด้วย index ใน loop
            $fields = $crawler->filter('.gsc_oci_field');
            $values = $crawler->filter('.gsc_oci_value');
    
            // ถ้า field/value ไม่เท่ากันอาจหมายถึงโครงสร้างหน้าเปลี่ยน
            // แต่ปกติแล้วถ้าเป็นหน้ารายละเอียด citation จะคู่กัน
            $count = min($fields->count(), $values->count());
    
            for ($i = 0; $i < $count; $i++) {
                $fieldLabel = trim($fields->eq($i)->text());
                $fieldValue = trim($values->eq($i)->text());
    
                // เช็คว่า label ตรงกับข้อมูลที่ต้องการหรือไม่
                if (stripos($fieldLabel, 'authors') !== false) {
                    $details['authors'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'publication date') !== false) {
                    $details['publication_date'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'journal') !== false) {
                    $details['journal'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'volume') !== false) {
                    $details['volume'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'issue') !== false) {
                    $details['issue'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'pages') !== false) {
                    $details['pages'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'publisher') !== false) {
                    $details['publisher'] = $fieldValue;
                } elseif (stripos($fieldLabel, 'description') !== false) {
                    $details['description'] = $fieldValue;
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'ไม่สามารถแยกข้อมูลได้: ' . $e->getMessage()
            ], 500);
        }
    
        return $details;
    }


    /**
     * Controller Method สำหรับรับ request และแสดงผลเป็น JSON
     * ตัวอย่าง URL: /scholar?first_name=Pongsathon&last_name=Janyoi
     */
    public function index(Request $request)
    {
        $firstName = $request->query('first_name');
        $lastName  = $request->query('last_name');
        if (empty(trim($firstName)) || empty(trim($lastName))) {
            return response()->json([
                'error' => 'กรุณาระบุชื่อและนามสกุลผ่าน query parameters (first_name, last_name)'
            ], 400);
        }
        $fullName = trim($firstName . ' ' . $lastName);
        $result = $this->getResearcherProfile($fullName);
        if ($result === null) {
            return response()->json([
                'error' => 'ไม่พบข้อมูลนักวิจัย หรือเกิดข้อผิดพลาดในการดึงข้อมูล'
            ], 404);
        }
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function testPaperHtml()
    {
        // กำหนด URL ของ paper ที่ต้องการทดสอบ
        $paperUrl = "https://scholar.google.com/citations?view_op=view_citation&hl=en&user=fn94QPIAAAAJ&pagesize=100&citation_for_view=fn94QPIAAAAJ:u-x6o8ySG0sC";

        // ใช้ handleRequest เพื่อส่ง GET request ไปยัง paper URL
        $response = $this->handleRequest($paperUrl);
        if (!$response) {
            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูลหน้า paper ได้'
            ], 500);
        }

        // อ่าน HTML จาก response
        $html = (string)$response->getBody();

        // บันทึก HTML ลงไฟล์ใน storage/logs สำหรับทดสอบ
        file_put_contents(storage_path('logs/paper_detail.html'), $html);

        return response()->json([
            'message' => 'บันทึก HTML ของ paper สำเร็จที่ storage/logs/paper_detail.html'
        ], 200);
    }
}
