<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaperDetailController extends Controller
{
    /**
     * ดึงข้อมูลรายละเอียดผลงานวิจัยจาก Google Scholar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPaperDetails(Request $request): JsonResponse
    {
        // URL ของหน้ารายละเอียดผลงาน
        $paperUrl = "https://scholar.google.com/"."citations?view_op=view_citation&hl=en&user=fn94QPIAAAAJ&pagesize=100&citation_for_view=fn94QPIAAAAJ:u-x6o8ySG0sC";

        // สร้าง client พร้อม disable SSL verification (หากจำเป็น)
        $client = new Client([
            'verify' => false,  // ปรับเป็น true หาก certificate ถูกต้อง
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; YourApp/1.0; +http://yourapp.example.com)'
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

        // สร้าง instance ของ DomCrawler ด้วย HTML ที่ได้มา
        $crawler = new Crawler($html);

        // กำหนด selector สำหรับดึงข้อมูลต่าง ๆ (อาจต้องปรับปรุงหากโครงสร้าง HTML มีการเปลี่ยนแปลง)
        // ตัวอย่างนี้ใช้ XPath ในการค้นหา element โดยอ้างอิงข้อความของ label
        try {
            $authors = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Authors')]]/div[@class='gsc_oci_value']")
                ->text());

            $publicationDate = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Publication date')]]/div[@class='gsc_oci_value']")
                ->text());

            $journal = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Journal')]]/div[@class='gsc_oci_value']")
                ->text());

            $volume = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Volume')]]/div[@class='gsc_oci_value']")
                ->text());

            $issue = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Issue')]]/div[@class='gsc_oci_value']")
                ->text());

            $pages = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Pages')]]/div[@class='gsc_oci_value']")
                ->text());

            $publisher = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Publisher')]]/div[@class='gsc_oci_value']")
                ->text());

            $description = trim($crawler
                ->filterXPath("//div[div[contains(text(),'Description')]]/div[@class='gsc_oci_value']")
                ->text());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'ไม่สามารถแยกข้อมูลได้: ' . $e->getMessage()
            ], 500);
        }

        // สร้างอาเรย์ข้อมูลเพื่อนำไปส่งออกในรูปแบบ JSON
        $data = [
            'authors'           => $authors,
            'publication_date'  => $publicationDate,
            'journal'           => $journal,
            'volume'            => $volume,
            'issue'             => $issue,
            'pages'             => $pages,
            'publisher'         => $publisher,
            'description'       => $description,
        ];

        return response()->json($data);
    }
}
