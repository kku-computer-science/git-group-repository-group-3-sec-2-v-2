<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use App\Models\Paper;
use App\Models\Source_data;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

// ต้อง import ScholarController ด้วย (ถ้าอยู่ใน namespace App\Http\Controllers\ScholarController)
use App\Http\Controllers\ScholarController;

class ScopuscallController extends Controller
{
    /**
     * ดึงข้อมูลจาก Scopus API แล้วบันทึกข้อมูล Paper พร้อมแนบความสัมพันธ์กับ User (ในตาราง pivot user_papers)
     * จากนั้นไปดึงข้อมูลจาก Google Scholar เพื่อนำ Paper มาเทียบและอัปเดต/บันทึกเช่นเดียวกัน
     *
     * @param  string  $id  รหัสเข้ารหัสของ User
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create($id)
    {
        // 1) ถอดรหัสและดึงข้อมูลผู้ใช้
        $userId = Crypt::decrypt($id);
        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // ---------------------------------------------------------------------
        //                             PART 1: SCOPUS
        // ---------------------------------------------------------------------
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
            return redirect()->back()->with('error', 'Failed to fetch Scopus search data.');
        }

        $entries = $searchResponse->json('search-results.entry');
        if (!$entries || !is_array($entries)) {
            return redirect()->back()->with('error', 'No papers found in Scopus.');
        }

        // ประมวลผลแต่ละ entry จาก Scopus
        foreach ($entries as $item) {
            // หาก paper นี้มีอยู่แล้วในฐานข้อมูล (ตรวจสอบจาก paper_name)
            $scopusPaperName = $item['dc:title'] ?? null;
            if (!$scopusPaperName) {
                continue; // ถ้าไม่มี title ข้าม
            }

            $existingPaper = Paper::where('paper_name', $scopusPaperName)->first();
            if ($existingPaper) {
                // แนบความสัมพันธ์กับ User ผ่านความสัมพันธ์ teacher() หากยังไม่แนบ
                if (!$existingPaper->teacher()->where('user_id', $user->id)->exists()) {
                    $existingPaper->teacher()->attach($user->id);
                }
                // หากต้องการอัปเดต citation, ปี หรืออื่น ๆ จาก Scopus ก็ทำได้ที่นี่
                // $existingPaper->paper_citation = $item['citedby-count'] ?? 0;
                // $existingPaper->save();
                continue;
            }

            // ดึง Scopus ID (ตัวอย่าง "SCOPUS_ID:85211026637")
            $rawScopusId = $item['dc:identifier'] ?? '';
            $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
            // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
            $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

            // ดึงข้อมูลรายละเอียดจาก Abstract API
            $detailResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get($detailUrl);

            // กำหนดค่าพื้นฐานจากผลการค้นหา
            $paper_name = $scopusPaperName;
            $abstract = null;
            $paper_funder = null;
            $detailData = [];
            if ($detailResponse->successful()) {
                $detailData = $detailResponse->json('abstracts-retrieval-response.item');
                // ถ้ามี citation-title ในรายละเอียด ให้ใช้แทน paper_name
                if (isset($detailData['bibrecord']['head']['citation-title'])) {
                    $paper_name = $detailData['bibrecord']['head']['citation-title'];
                }
                // ดึง abstract
                if (isset($detailData['bibrecord']['head']['abstracts'])) {
                    $abstract = $detailData['bibrecord']['head']['abstracts'];
                }
                // ดึงข้อมูล paper_funder (จาก xocs:funding-text)
                if (isset($detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                    $paper_funder = $detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                }
            }

            // กำหนด paper_url: ใช้ link[0]['@href'] หรือ fallback เป็น $detailUrl
            $paper_url = $item['link'][0]['@href'] ?? $detailUrl;
            // ดึงปีจาก prism:coverDate (เอา 4 ตัวแรก)
            $coverDate = $item['prism:coverDate'] ?? null;
            $paper_yearpub = $coverDate ? substr($coverDate, 0, 4) : null;

            // สร้าง instance ใหม่ของ Paper และบันทึก
            $paper = new Paper;
            $paper->paper_name        = $paper_name;
            $paper->abstract          = $abstract;
            $paper->paper_type        = $item['prism:aggregationType'] ?? 'Journal';
            $paper->paper_subtype     = $item['subtype'] ?? 'ar';
            $paper->paper_sourcetitle = $item['subtypeDescription'] ?? 'Article';
            $paper->keyword           = isset($item['author-keywords']) ? json_encode($item['author-keywords'], JSON_UNESCAPED_UNICODE) : null;
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

            // --- แนบข้อมูลผู้แต่ง ---
            $authorsData = $detailData['bibrecord']['head']['author-group']['author']
                ?? $detailResponse->json('abstracts-retrieval-response.authors.author');
            if ($authorsData && !is_array($authorsData)) {
                $authorsData = [$authorsData];
            }
            if ($authorsData && is_array($authorsData)) {
                $totalAuthors = count($authorsData);
                $x = 1;
                foreach ($authorsData as $authorItem) {
                    $givenName = $authorItem['ce:given-name'] ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                    $surname = $authorItem['ce:surname'] ?? ($authorItem['preferred-name']['ce:surname'] ?? '');
                    $trimmedName = trim($givenName . ' ' . $surname);

                    if ($x === 1) {
                        $author_type = 1; // first
                    } elseif ($x === $totalAuthors) {
                        $author_type = 3; // last
                    } else {
                        $author_type = 2; // co-author
                    }

                    // ตรวจสอบในตาราง users
                    $existingUser = User::where('fname_en', $givenName)
                        ->where('lname_en', $surname)
                        ->first();

                    if ($existingUser) {
                        $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                    } else {
                        // หากไม่พบในตาราง users ให้ตรวจสอบในตาราง authors
                        $existingAuthor = Author::where('author_fname', $givenName)
                            ->where('author_lname', $surname)
                            ->first();
                        if (!$existingAuthor) {
                            $newAuthor = new Author;
                            $newAuthor->author_fname = $givenName;
                            $newAuthor->author_lname = $surname;
                            $newAuthor->save();
                            // แนบผ่านความสัมพันธ์ author()
                            $paper->author()->attach($newAuthor->id, ['author_type' => $author_type]);
                        } else {
                            $paper->author()->attach($existingAuthor->id, ['author_type' => $author_type]);
                        }
                    }
                    $x++;
                }
            }

            // สุดท้าย: แนบความสัมพันธ์ paper กับ User ที่สั่งดึง
            $paper->teacher()->attach($user->id);
        }

        // ---------------------------------------------------------------------
        //                          PART 2: GOOGLE SCHOLAR
        // ---------------------------------------------------------------------
        // เรียกใช้งาน ScholarController เพื่อดึงข้อมูลจาก Google Scholar
        $scholar = new ScholarController();
        // สมมติว่าใน ScholarController นั้นมีเมธอด getResearcherProfile($fullName)
        $fullName = trim($user->fname_en . ' ' . $user->lname_en);
        $scholarData = $scholar->getResearcherProfile($fullName);

        // ถ้า null แปลว่าไม่เจอหรือดึงไม่ได้
        if ($scholarData === null) {
            // อาจจะแค่แจ้งเตือน หรือจะไม่ทำอะไรก็ได้
            // return redirect()->back()->with('warning', 'ไม่พบข้อมูลจาก Google Scholar.');
        } else {
            // วน loop ที่ $scholarData['publications'] เพื่อตรวจสอบว่า paper มีหรือไม่
            if (isset($scholarData['publications']) && is_array($scholarData['publications'])) {
                foreach ($scholarData['publications'] as $publication) {
                    $title       = $publication['title'] ?? null;
                    if (!$title) {
                        continue; // ไม่มีชื่อก็ข้าม
                    }
                    $paperUrl    = $publication['paper_url'] ?? null;
                    $venue       = $publication['venue'] ?? null;      // อาจเป็น conference/journal
                    $year        = $publication['year'] ?? null;
                    $citations   = (int)($publication['citations'] ?? 0);
                    $authors     = $publication['authors'] ?? [];      // array ชื่อผู้แต่ง
                    $details     = $publication['details'] ?? [];      // รายละเอียดเพิ่มเติม (journal, volume, pages, publisher ฯลฯ)

                    // เช็คว่ามี paper อยู่ใน DB แล้วหรือไม่
                    $existingPaper = Paper::where('paper_name', $title)->first();
                    if ($existingPaper) {
                        // ถ้ามีอยู่แล้ว อัปเดต pivot user_papers ถ้ายังไม่ได้ผูก
                        if (!$existingPaper->teacher()->where('user_id', $user->id)->exists()) {
                            $existingPaper->teacher()->attach($user->id);
                        }
                        // อัปเดตจำนวน citation หรือปี ตรงนี้ขึ้นกับว่าอยากอัปเดตหรือไม่
                        // เช่น เราอาจให้อิงค่า citation จาก Scopus หรือ Scholar ก็ได้
                        $existingPaper->paper_citation = max($existingPaper->paper_citation, $citations);
                        if ($year !== 'N/A' && (int)$year > 0) {
                            $existingPaper->paper_yearpub = $year; // กรณีอยากอัปเดตปี
                        }
                        $existingPaper->save();

                    } else {
                        // ถ้าไม่มีใน DB ให้สร้างใหม่ (เหมือนตอน Scopus)
                        $paper = new Paper;
                        $paper->paper_name    = $title;
                        $paper->abstract      = null; // Scholar ไม่ค่อยให้ abstract
                        $paper->paper_type    = 'Scholar'; // หรือ 'Journal' ก็ได้ (ไม่มีใน Scholar)
                        $paper->paper_subtype = 'ar';      // สมมติ
                        // อาจเก็บ venue ที่ Scholar ให้ไว้ใน paper_sourcetitle
                        $paper->paper_sourcetitle = $venue ?? 'N/A';
                        // Google Scholar ไม่มี keywords ตรง ๆ จึงเซ็ตเป็น null
                        $paper->keyword       = null;
                        $paper->paper_url     = $paperUrl;
                        $paper->publication   = $venue;
                        $paper->paper_yearpub = ($year !== 'N/A') ? $year : null;
                        $paper->paper_volume  = $details['volume'] ?? null;
                        $paper->paper_issue   = $details['issue'] ?? '-';
                        $paper->paper_citation = $citations;
                        $paper->paper_page    = $details['pages'] ?? '-';
                        // Scholar ไม่มี DOI หรือ funder
                        $paper->paper_doi     = null;
                        $paper->paper_funder  = null;
                        $paper->reference_number = null;
                        $paper->save();

                        // สมมติว่าเอา Source_data id=2 ไว้เป็น Google Scholar
                        $sourceScholar = Source_data::find(2);
                        if ($sourceScholar) {
                            $paper->source()->sync([$sourceScholar->id]);
                        }

                        // แนบ authors (แบบง่าย ๆ)
                        // สังเกตใน $authors จะเป็น array ที่รวมทุกชื่อ เช่น ["F Example", "S Someone", ...]
                        // ซึ่งเราต้อง split ชื่อ-นามสกุลเอง หรือจะแมปเทียบใน users, authors ก็ได้
                        $totalAuthors = count($authors);
                        $x = 1;
                        foreach ($authors as $authName) {
                            // สมมติชื่อเต็มเป็น "Firstname Lastname"
                            $split = explode(' ', $authName);
                            $givenName = trim($split[0] ?? '');
                            $surname   = '';
                            if (count($split) > 1) {
                                // ดึงส่วนที่เหลือทั้งหมดเป็นนามสกุล
                                $surname = trim(implode(' ', array_slice($split, 1)));
                            }

                            if ($x === 1) {
                                $author_type = 1;
                            } elseif ($x === $totalAuthors) {
                                $author_type = 3;
                            } else {
                                $author_type = 2;
                            }

                            if ($givenName || $surname) {
                                // ลองหาใน users
                                $existingUser = User::where('fname_en', $givenName)
                                    ->where('lname_en', $surname)
                                    ->first();
                                if ($existingUser) {
                                    $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                                } else {
                                    // หาใน authors
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

                        // แนบความสัมพันธ์ paper กับ User
                        $paper->teacher()->attach($user->id);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Scopus & Scholar data processed and saved.');
    }

    /**
     * ตัวอย่างการแสดงสถิติ paper ตามปี (5 ปีล่าสุด)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $year = range(Carbon::now()->year - 5, Carbon::now()->year);
        $paperCount = [];
        foreach ($year as $value) {
            $paperCount[] = Paper::where(DB::raw('YEAR(paper_yearpub)'), $value)->count();
        }
        return view('test')
            ->with('year', json_encode($year, JSON_NUMERIC_CHECK))
            ->with('paper', json_encode($paperCount, JSON_NUMERIC_CHECK));
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id) {}

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
