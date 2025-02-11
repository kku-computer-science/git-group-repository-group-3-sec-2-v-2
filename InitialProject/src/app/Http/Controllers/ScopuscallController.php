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
     * ดึงข้อมูลจาก Scopus API แล้วบันทึกข้อมูล Paper พร้อมแนบความสัมพันธ์กับ User
     * โดยจะบันทึกเฉพาะกรณี paper ใหม่ (Insert) เท่านั้น ส่วนของ update หรือ add relation
     * (ถ้าพบว่ามีในฐานข้อมูลอยู่แล้ว) จะไม่สนใจ
     *
     * @param  string  $id  (Encrypt) ของ User
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

        // สร้าง array สำหรับเก็บข้อมูลว่าอันไหน insert บ้าง
        $insertedPapers = [];

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
                // (ตัวอย่างนี้จะไม่สนใจ update หรือ add relation ใด ๆ)
                // ถ้าต้องการทำ update หรือ add pivot user-papers ก็สามารถเพิ่มได้
                // แต่โจทย์บอกว่าเอาเฉพาะ insert จึงข้าม
                continue;
            }

            // ---- ถ้าไม่เจอใน DB => ทำการ Insert ใหม่ ----
            $rawScopusId = $item['dc:identifier'] ?? '';
            $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
            // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
            $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

            // ดึงข้อมูลรายละเอียดจาก Abstract API
            $detailResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get($detailUrl);

            // กำหนดค่าพื้นฐาน
            $paper_name = $scopusPaperName;
            $abstract = null;
            $paper_funder = null;
            $detailData = [];

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

            // แนบข้อมูล Source_data (สมมติว่า id=1 เป็น Scopus)
            $source = Source_data::find(1);
            if ($source) {
                $paper->source()->sync([$source->id]);
            }

            // --- แนบข้อมูลผู้แต่ง (authors) ---
            $authorsData = $detailData['bibrecord']['head']['author-group']['author']
                ?? $detailResponse->json('abstracts-retrieval-response.authors.author');

            if ($authorsData && !is_array($authorsData)) {
                $authorsData = [$authorsData];
            }

            if ($authorsData && is_array($authorsData)) {
                $totalAuthors = count($authorsData);
                $x = 1;
                foreach ($authorsData as $authorItem) {
                    $givenName = $authorItem['ce:given-name']
                        ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                    $surname   = $authorItem['ce:surname']
                        ?? ($authorItem['preferred-name']['ce:surname'] ?? '');

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
                        // หากไม่พบใน users ให้ตรวจสอบในตาราง authors
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
                    $x++;
                }
            }

            // แนบความสัมพันธ์กับ User ที่เรียก
            $paper->teacher()->attach($user->id);

            // เก็บชื่อ paper ลงในอาเรย์ "insertedPapers"
            $insertedPapers[] = $paper->paper_name;
        }

        // ส่งข้อมูล insertedPapers กลับไปเป็น session เพื่อแจ้งเตือนใน view
        return redirect()->back()->with([
            'insertedPapers' => $insertedPapers,
        ]);
    }

    /**
     * ตัวอย่างการแสดงสถิติ paper ตามปี (5 ปีล่าสุด)
     */
    public function index()
    {
        $year = range(Carbon::now()->year - 5, Carbon::now()->year);
        $paperCount = [];
        foreach ($year as $value) {
            $paperCount[] = Paper::whereYear('paper_yearpub', $value)->count();
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
