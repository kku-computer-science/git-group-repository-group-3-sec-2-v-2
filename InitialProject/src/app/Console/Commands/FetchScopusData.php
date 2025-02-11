<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Author;
use App\Models\User;
use App\Models\Paper;
use App\Models\Source_data;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchScopusData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scopus:fetch2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Scopus API data for all users and save papers with relationships';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // ดึงเฉพาะผู้ใช้ที่มี academic rank (is_research = 1)
        $users = User::where('is_research', 1)->get();
        if ($users->isEmpty()) {
            Log::error("No users with academic ranks found");
            $this->info('ไม่พบผู้ใช้งานในระบบ');
            return 0;
        }

        foreach ($users as $user) {
            $this->info("กำลังประมวลผลข้อมูลสำหรับผู้ใช้: {$user->id} - {$user->fname_en} {$user->lname_en}");
            $this->processUserScopusData($user);
        }

        $this->info('ประมวลผลข้อมูล Scopus สำหรับผู้ใช้ทุกคนเรียบร้อยแล้ว');
        return 0;
    }

    /**
     * ดึงและประมวลผลข้อมูล Scopus สำหรับผู้ใช้แต่ละคน
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function processUserScopusData(User $user)
    {
        // สร้าง search query โดยใช้ตัวอักษรตัวแรกของ fname_en กับ lname_en
        $firstLetter = substr($user->fname_en, 0, 1);
        $lname = $user->lname_en;
        $searchQuery = "AUTHOR-NAME({$lname},{$firstLetter})";

        // เรียก Scopus Search API
        $searchResponse = Http::withHeaders([
            'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
            'Accept'       => 'application/json',
        ])->get("https://api.elsevier.com/content/search/scopus", [
            'query' => $searchQuery,
        ]);

        if (!$searchResponse->successful()) {
            $this->error("ไม่สามารถดึงข้อมูล Scopus สำหรับผู้ใช้: {$user->id}");
            return;
        }

        $entries = $searchResponse->json('search-results.entry');
        if (!$entries || !is_array($entries)) {
            $this->info("ไม่พบ paper สำหรับผู้ใช้: {$user->id}");
            return;
        }

        // ประมวลผลแต่ละ entry
        foreach ($entries as $item) {
            // ใช้ชื่อ paper อย่างปลอดภัย
            $paper_name = $item['dc:title'] ?? $item['title'] ?? 'Untitled Paper';

            // หากมี paper นี้อยู่แล้วในฐานข้อมูล เราจะข้ามการประมวลผล paper นี้
            if (Paper::where('paper_name', $paper_name)->exists()) {
                continue;
            }

            // ดึง Scopus ID จากผลการค้นหา (เช่น "SCOPUS_ID:85211026637")
            $rawScopusId = $item['dc:identifier'] ?? '';
            $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
            if (!$scopusId) {
                continue;
            }
            
            // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
            $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

            // ดึงข้อมูลรายละเอียดจาก Abstract API
            $detailResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get($detailUrl);

            // กำหนดค่าพื้นฐานจากผลการค้นหา
            $abstract = null;
            $paper_funder = null;
            $detailData = [];
            if ($detailResponse->successful()) {
                $detailData = $detailResponse->json('abstracts-retrieval-response.item');

                // หากมี citation-title ในรายละเอียด ให้ใช้แทน paper_name
                if (isset($detailData['bibrecord']['head']['citation-title'])) {
                    $titleValue = $detailData['bibrecord']['head']['citation-title'];
                    $paper_name = is_array($titleValue)
                        ? json_encode($titleValue, JSON_UNESCAPED_UNICODE)
                        : $titleValue;
                }

                // ดึง abstract (ตรวจสอบหากเป็น array ให้แปลงเป็น JSON)
                if (isset($detailData['bibrecord']['head']['abstracts'])) {
                    $abstractValue = $detailData['bibrecord']['head']['abstracts'];
                    $abstract = is_array($abstractValue)
                        ? json_encode($abstractValue, JSON_UNESCAPED_UNICODE)
                        : $abstractValue;
                }

                // ดึงข้อมูล paper_funder (จาก xocs:funding-text)
                if (isset($detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                    $fundingValue = $detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                    $paper_funder = is_array($fundingValue)
                        ? json_encode($fundingValue, JSON_UNESCAPED_UNICODE)
                        : $fundingValue;
                }
            }

            // กำหนด paper_url: ใช้ link[0]['@href'] จากผลการค้นหาหรือใช้ detailUrl เป็น fallback
            $paper_url = $item['link'][0]['@href'] ?? $detailUrl;
            
            // ดึงปีจาก prism:coverDate (เอา 4 ตัวแรก)
            $coverDate = $item['prism:coverDate'] ?? null;
            $paper_yearpub = $coverDate ? substr($coverDate, 0, 4) : null;

            // สร้าง instance ใหม่ของ Paper และแม็ปฟิลด์ต่างๆ
            $paper = new Paper;
            $paper->paper_name        = $paper_name;
            $paper->abstract          = $abstract;
            $paper->paper_type        = $item['prism:aggregationType'] ?? 'Journal';
            $paper->paper_subtype     = $item['subtype'] ?? 'ar';
            $paper->paper_sourcetitle = $item['subtypeDescription'] ?? 'Article';
            $paper->keyword           = $item['author-keywords'] ?? null;
            $paper->paper_url         = $paper_url;
            $paper->publication       = $item['prism:publicationName'] ?? null;
            $paper->paper_yearpub     = $paper_yearpub;
            $paper->paper_volume      = $item['prism:volume'] ?? null;
            $paper->paper_issue       = $item['prism:issueIdentifier'] ?? '-';
            $paper->paper_citation    = $item['citedby-count'] ?? 0;
            $paper->paper_page        = $item['prism:pageRange'] ?? '-';
            $paper->paper_doi         = $item['prism:doi'] ?? null;
            $paper->paper_funder      = $paper_funder;
            $paper->reference_number  = null;
            $paper->save();

            // แนบข้อมูล Source (ในที่นี้ใช้ Source_data id=1)
            $source = Source_data::find(1);
            if ($source) {
                $paper->source()->sync([$source->id]);
            }

            /*
             * --- ประมวลผลและแนบข้อมูลผู้แต่ง ---
             *
             * หาก API ส่งข้อมูลในรูปแบบ author-group (ซึ่งมี affiliation อยู่ด้วย)
             * ให้แปลงเป็นรายการเดียวในตัวแปร $authorsList
             */
            $authorsList = [];
            if (isset($detailData['bibrecord']['head']['author-group'])) {
                $authorGroups = $detailData['bibrecord']['head']['author-group'];
                if (isset($authorGroups[0])) {
                    // ถ้าเป็น array ของกลุ่ม
                    foreach ($authorGroups as $group) {
                        if (isset($group['author'])) {
                            if (is_array($group['author'])) {
                                foreach ($group['author'] as $authorItem) {
                                    // เพิ่มข้อมูล affiliation จาก group เข้าไปใน author
                                    $authorItem['affiliation'] = $group['affiliation'] ?? null;
                                    $authorsList[] = $authorItem;
                                }
                            } else {
                                $authorItem = $group['author'];
                                $authorItem['affiliation'] = $group['affiliation'] ?? null;
                                $authorsList[] = $authorItem;
                            }
                        }
                    }
                } else {
                    // กลุ่มเดียว
                    $group = $authorGroups;
                    if (isset($group['author'])) {
                        if (is_array($group['author'])) {
                            foreach ($group['author'] as $authorItem) {
                                $authorItem['affiliation'] = $group['affiliation'] ?? null;
                                $authorsList[] = $authorItem;
                            }
                        } else {
                            $authorItem = $group['author'];
                            $authorItem['affiliation'] = $group['affiliation'] ?? null;
                            $authorsList[] = $authorItem;
                        }
                    }
                }
            } else {
                // Fallback: ใช้ข้อมูลจาก key อื่นๆ (ถ้าไม่มี author-group)
                $authorsData = $detailResponse->json('abstracts-retrieval-response.authors.author') ?? [];
                if (!is_array($authorsData)) {
                    $authorsData = [$authorsData];
                }
                $authorsList = $authorsData;
            }

            // ตัวแปร flag เพื่อบันทึกว่ามี user (หรือ teacher) อยู่ในรายชื่อผู้แต่งหรือไม่
            $foundTeacher = false;
            $totalAuthors = count($authorsList);
            $x = 1;
            foreach ($authorsList as $authorItem) {
                // ดึงชื่อผู้แต่งจาก API โดยใช้ ce:given-name กับ ce:surname (หรือจาก preferred-name ถ้าไม่มี)
                $givenName = $authorItem['ce:given-name'] ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                $surname = $authorItem['ce:surname'] ?? ($authorItem['preferred-name']['ce:surname'] ?? '');
                
                // กำหนด author_type: ตัวแรกเป็น 1, ตัวสุดท้ายเป็น 3, คนกลางเป็น 2
                if ($x === 1) {
                    $author_type = 1;
                } elseif ($x === $totalAuthors) {
                    $author_type = 3;
                } else {
                    $author_type = 2;
                }

                // ตรวจสอบว่า author จาก APIตรงกับ user ที่ค้นหาหรือไม่
                if ($this->isUserMatch($user, $givenName, $surname)) {
                    $foundTeacher = true;
                    if (!$paper->teacher()->where('user_id', $user->id)->exists()) {
                        $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                    }
                } else {
                    // ตรวจสอบ affiliation ว่ามีองค์กร 'Khon Kaen University' หรือไม่
                    $isInKhonKaen = false;
                    if (isset($authorItem['affiliation']) && isset($authorItem['affiliation']['organization'])) {
                        $orgData = $authorItem['affiliation']['organization'];
                        if (isset($orgData['$'])) {
                            // กรณีมีค่าเดียว
                            if (stripos($orgData['$'], 'khon kaen university') !== false) {
                                $isInKhonKaen = true;
                            }
                        } elseif (is_array($orgData)) {
                            foreach ($orgData as $org) {
                                if (isset($org['$']) && stripos($org['$'], 'khon kaen university') !== false) {
                                    $isInKhonKaen = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isInKhonKaen) {
                        // หาก affiliation อยู่ใน Khon Kaen University ให้ถือว่า user นั้นเป็น teacherด้วย
                        $foundTeacher = true;
                        if (!$paper->teacher()->where('user_id', $user->id)->exists()) {
                            $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                        }
                    } else {
                        // สำหรับผู้แต่งที่ไม่ได้เป็น user (หรือไม่ได้มี affiliation จาก Khon Kaen University)
                        // ให้ upload ลงในตาราง authors พร้อมแนบความสัมพันธ์ใน pivot ของ author_of_papers
                        $existingAuthor = Author::where('author_fname', $givenName)
                            ->where('author_lname', $surname)
                            ->first();
                        if (!$existingAuthor) {
                            $newAuthor = new Author;
                            $newAuthor->author_fname = $givenName;
                            $newAuthor->author_lname = $surname;
                            $newAuthor->save();
                            $paper->author()->attach($newAuthor->id, ['author_type' => $author_type]);
                        } else {
                            $paper->author()->attach($existingAuthor->id, ['author_type' => $author_type]);
                        }
                    }
                }
                $x++;
            }

            // เฉพาะเมื่อมี user (หรือ teacher) ใน paper เท่านั้นที่จะบันทึก paper ลงฐานข้อมูล
            if (!$foundTeacher) {
                $this->info("User {$user->fname_en} {$user->lname_en} ไม่ปรากฏในรายชื่อผู้แต่งของ paper: {$paper_name} จึงไม่บันทึก paper นี้");
                $paper->delete();
            }
        }
    }

    /**
     * ตรวจสอบว่า author จาก API ตรงกับ user ที่ค้นหาหรือไม่
     *
     * เปรียบเทียบแบบตรงตัวระหว่าง ce:given-name กับ ce:surname กับ user->fname_en กับ user->lname_en
     *
     * @param \App\Models\User $user
     * @param string $givenName ชื่อที่ได้จาก API (ce:given-name)
     * @param string $surname   นามสกุลที่ได้จาก API (ce:surname)
     * @return bool
     */
    private function isUserMatch(User $user, $givenName, $surname)
    {
        $userFname = strtolower(trim($user->fname_en));
        $userLname = strtolower(trim($user->lname_en));

        $apiFname = strtolower(trim($givenName));
        $apiLname = strtolower(trim($surname));

        return ($userFname === $apiFname) && ($userLname === $apiLname);
    }
}
