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

class ScopuscallController extends Controller
{
    /**
     * ดึงข้อมูลจาก Scopus API แล้วบันทึกข้อมูล Paper พร้อมแนบความสัมพันธ์กับ User (ในตาราง pivot user_papers)
     *
     * @param  string  $id  รหัสเข้ารหัสของ User
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create($id)
    {
        // ถอดรหัสและดึงข้อมูลผู้ใช้
        $userId = Crypt::decrypt($id);
        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

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
            return redirect()->back()->with('error', 'No papers found.');
        }

        // ประมวลผลแต่ละ entry
        foreach ($entries as $item) {
            // หาก paper นี้มีอยู่แล้วในฐานข้อมูล (ตรวจสอบจาก paper_name ที่ได้จาก dc:title)
            if (Paper::where('paper_name', $item['dc:title'])->exists()) {
                $existingPaper = Paper::where('paper_name', $item['dc:title'])->first();
                // แนบความสัมพันธ์กับ User ผ่านความสัมพันธ์ teacher() หากยังไม่แนบ
                if (!$existingPaper->teacher()->where('user_id', $user->id)->exists()) {
                    $existingPaper->teacher()->attach($user->id);
                }
                continue;
            }

            // ดึง Scopus ID จากผลการค้นหา (ตัวอย่าง "SCOPUS_ID:85211026637")
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
            $paper_name = $item['dc:title'] ?? null;
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
                        $author_type = 1;
                    } elseif ($x === $totalAuthors) {
                        $author_type = 3;
                    } else {
                        $author_type = 2;
                    }

                    // ตรวจสอบในตาราง users
                    $existingUser = User::where('fname_en', $givenName)
                        ->where('lname_en', $surname)
                        ->first();

                    if ($existingUser) {
                        // ใช้ความสัมพันธ์ teacher() หากเจอในตาราง users
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
                            // แนบข้อมูลผ่านความสัมพันธ์ author() (pivot table author_of_papers)
                            $paper->author()->attach($newAuthor->id, ['author_type' => $author_type]);
                        } else {
                            $paper->author()->attach($existingAuthor->id, ['author_type' => $author_type]);
                        }
                    }
                    $x++;
                }
            }
        }

        return redirect()->back()->with('success', 'Scopus data processed and saved.');
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
