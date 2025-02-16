<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Paper;
use App\Models\Author;
use App\Models\Source_data;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ScopusFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scopus:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Scopus for all users with academic ranks (is_research=1).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1) ดึงเฉพาะผู้ใช้ที่มี academic rank (is_research = 1)
        $users = User::where('is_research', 1)->get();
        if ($users->isEmpty()) {
            Log::error("No users with academic ranks found");
            $this->info('ไม่พบผู้ใช้งานในระบบ');
            return 0;
        }

        // 2) วนลูปทีละ user แล้วทำ Logic การเรียก Scopus API
        foreach ($users as $user) {
            // -- สร้าง array สำหรับเก็บข้อมูล Paper ที่ insert สำเร็จ --
            $insertedPapers = [];

            // ---------------------------------------------------------------
            //             PART 1: SCOPUS (ค้นหา + Insert Papers)
            // ---------------------------------------------------------------
            // สร้าง search query โดยใช้ตัวอักษรตัวแรกของ fname_en กับ lname_en
            $firstLetter = substr($user->fname_en, 0, 1);
            $lname       = $user->lname_en;
            $searchQuery = "AUTHOR-NAME({$lname},{$firstLetter})";

            // เรียก Scopus Search API
            $searchResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963', // ใส่ API Key ของคุณ
                'Accept'       => 'application/json',
            ])->get("https://api.elsevier.com/content/search/scopus", [
                'query' => $searchQuery,
            ]);

            if (!$searchResponse->successful()) {
                // log error
                Log::error("Failed to fetch Scopus search data for user_id: {$user->id}");
                continue; // ข้าม user นี้ไปทำ user ถัดไป
            }

            $entries = $searchResponse->json('search-results.entry');
            if (!$entries || !is_array($entries)) {
                // ไม่มี paper ใน scopus สำหรับ user นี้
                continue;
            }

            // ประมวลผลแต่ละ entry จาก Scopus
            foreach ($entries as $item) {
                // 1) ตรวจสอบชื่อ Paper (title)
                $scopusPaperName = $item['dc:title'] ?? null;
                if (!$scopusPaperName) {
                    continue; // ถ้าไม่มี title ข้าม
                }

                // 2) เช็คว่ามี Paper นี้ใน DB แล้วหรือไม่
                $existingPaper = Paper::where('paper_name', $scopusPaperName)->first();
                if ($existingPaper) {
                    // ไม่ทำอะไร เพราะสนใจเฉพาะการ insert ใหม่
                    continue;
                }

                // 3) ถ้าไม่มี => เตรียม Insert ใหม่
                $rawScopusId = $item['dc:identifier'] ?? '';
                $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
                // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
                $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

                // ดึงข้อมูลรายละเอียดจาก Abstract API
                $detailResponse = Http::withHeaders([
                    'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963', // ใส่ API Key ของคุณ
                    'Accept'       => 'application/json',
                ])->get($detailUrl);

                // กำหนดค่าพื้นฐานก่อน
                $paper_name   = $scopusPaperName;
                $abstract     = null;
                $paper_funder = null;
                $detailData   = [];

                if ($detailResponse->successful()) {
                    $detailData = $detailResponse->json('abstracts-retrieval-response.item');

                    // ถ้ามี citation-title -> ใช้แทน paper_name
                    if (isset($detailData['bibrecord']['head']['citation-title'])) {
                        $paper_name = $detailData['bibrecord']['head']['citation-title'];
                    }

                    // ดึง abstract (เช็คก่อนว่าเป็น array หรือ string)
                    if (isset($detailData['bibrecord']['head']['abstracts'])) {
                        $abs = $detailData['bibrecord']['head']['abstracts'];
                        $abstract = is_array($abs)
                            ? json_encode($abs, JSON_UNESCAPED_UNICODE)
                            : $abs;
                    }

                    // ดึงข้อมูล paper_funder (จาก xocs:funding-text)
                    if (isset($detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                        $funderRaw = $detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                        $paper_funder = is_array($funderRaw)
                            ? json_encode($funderRaw, JSON_UNESCAPED_UNICODE)
                            : $funderRaw;
                    }
                }

                // 4) กำหนด paper_url (พยายามเอา link['@href'] ถ้ามี)
                $paper_url = $detailUrl; // fallback เริ่มต้น
                if (!empty($item['link']) && is_array($item['link'])) {
                    foreach ($item['link'] as $linkObj) {
                        if (isset($linkObj['@ref']) && $linkObj['@ref'] === 'scopus') {
                            $paper_url = $linkObj['@href'] ?? $detailUrl;
                            break;
                        }
                    }
                }

                // 5) ดึงปีจาก prism:coverDate (เอา 4 ตัวแรก)
                $coverDate = $item['prism:coverDate'] ?? null;
                $paper_yearpub = $coverDate ? substr($coverDate, 0, 4) : null;

                // 6) แปลง subtype 'ar' เป็น 'Article'
                $subtype = $item['subtype'] ?? 'ar';
                if ($subtype === 'ar') {
                    $subtype = 'Article';
                }

                // 7) สร้าง instance ของ Paper และบันทึก
                $paper = new Paper;
                $paper->paper_name        = $paper_name;
                $paper->abstract          = $abstract;
                $paper->paper_type        = $item['prism:aggregationType'] ?? 'Journal';
                $paper->paper_subtype     = $subtype;

                $subtypeDesc = $item['subtypeDescription'] ?? 'Article';
                if (is_array($subtypeDesc)) {
                    $subtypeDesc = json_encode($subtypeDesc, JSON_UNESCAPED_UNICODE);
                }
                $paper->paper_sourcetitle = $subtypeDesc;

                // author-keywords (array -> json_encode)
                if (!empty($item['author-keywords'])) {
                    $paper->keyword = json_encode($item['author-keywords'], JSON_UNESCAPED_UNICODE);
                } else {
                    $paper->keyword = null;
                }

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

                // 8) แนบข้อมูล Source_data (สมมติว่า id=1 คือ Scopus)
                $source = Source_data::find(1);
                if ($source) {
                    $paper->source()->sync([$source->id]);
                }

                // -------------------------------------------------------
                // 9) เพิ่มข้อมูลผู้แต่ง (Authors) ตามลำดับจาก Scopus
                // -------------------------------------------------------
                $authorsData = $detailData['bibrecord']['head']['author-group']['author']
                    ?? $detailResponse->json('abstracts-retrieval-response.authors.author');

                // หากเจอเป็น Object เดียว => ใส่ใน array
                if ($authorsData && !is_array($authorsData)) {
                    $authorsData = [$authorsData];
                }

                // ไว้เช็คว่า user คนนี้อยู่ในรายชื่อ authors หรือไม่
                $isUserAuthorFound = false;

                if ($authorsData && is_array($authorsData)) {
                    $totalAuthors = count($authorsData);

                    foreach ($authorsData as $i => $authorItem) {
                        // ดึงชื่อ-นามสกุล
                        $givenName = $authorItem['ce:given-name']
                            ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                        $surname   = $authorItem['ce:surname']
                            ?? ($authorItem['preferred-name']['ce:surname'] ?? '');

                        // กำหนด author_type (1=first, 2=co-author, 3=last)
                        if ($i == 0) {
                            $author_type = 1; // first
                        } elseif ($i == $totalAuthors - 1) {
                            $author_type = 3; // last
                        } else {
                            $author_type = 2; // co-author
                        }

                        // ------------------------------------------------
                        //   เช็คว่าเป็น user คนนี้หรือไม่
                        //   => ต้องผ่าน 3 เงื่อนไข: 
                        //      (a) อักษรแรกของชื่อ ตรงกับ user
                        //      (b) นามสกุล ตรงกับ user
                        //      (c) affiliation มี "Khon Kaen"
                        // ------------------------------------------------
                        $isSameUser = false;

                        $firstApi  = strtolower(substr($givenName, 0, 1));
                        $firstUser = strtolower(substr($user->fname_en, 0, 1));

                        // ต้องตรงทั้ง 3 เงื่อนไข
                        if ($firstApi === $firstUser &&
                            strcasecmp($surname, $user->lname_en) === 0
                        ) {
                            // (c) เช็ค affiliation
                            if (!empty($authorItem['affiliation'])) {
                                // ถ้าเป็น object เดียว
                                if (isset($authorItem['affiliation']['city'])) {
                                    $affCity = $authorItem['affiliation']['city'];
                                    if (stripos($affCity, 'Khon Kaen') !== false) {
                                        $isSameUser = true;
                                    }
                                } 
                                // ถ้าเป็น array
                                elseif (is_array($authorItem['affiliation'])) {
                                    foreach ($authorItem['affiliation'] as $aff) {
                                        // ตรวจ city หรือ affiliation-city
                                        $affCity = $aff['city']
                                            ?? $aff['affiliation-city']
                                            ?? '';
                                        if (stripos($affCity, 'Khon Kaen') !== false) {
                                            $isSameUser = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // ถ้าใช่ user นี้ => attach
                        if ($isSameUser) {
                            $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                            $isUserAuthorFound = true;
                        } else {
                            // ถ้าไม่ใช่ user นี้ => เช็ค user อื่น หรือ author ใหม่
                            $existingUser = User::where('fname_en', $givenName)
                                ->where('lname_en', $surname)
                                ->first();

                            if ($existingUser) {
                                $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                            } else {
                                // ดูในตาราง authors
                                $existingAuthor = Author::where('author_fname', $givenName)
                                    ->where('author_lname', $surname)
                                    ->first();

                                if (!$existingAuthor) {
                                    // เพิ่มใหม่
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
                    } // end foreach authorsData
                } // end if authorsData

                // 10) ถ้า user นี้ไม่อยู่ในรายชื่อ authors
                //     แต่เราต้องการบันทึกว่า user เป็นผู้นำเข้า paper
                if (!$isUserAuthorFound) {
                    // ใส่ author_type=0 หรือคอลัมน์อื่นตามต้องการ
                    $paper->teacher()->attach($user->id, ['author_type' => 0]);
                }

                // สุดท้าย เก็บชื่อ paper ลงใน "insertedPapers"
                $insertedPapers[] = $paper->paper_name;
            } // end foreach entries

            // สรุปว่าผู้ใช้คนนี้ import ได้กี่ paper
            $countInserted = count($insertedPapers);
            if ($countInserted > 0) {
                $this->info("User {$user->id} ({$user->fname_en} {$user->lname_en}) imported {$countInserted} papers.");
                Log::info("User {$user->id} imported {$countInserted} papers from Scopus.");
            }
        } // end foreach user

        return 0;
    }
}
