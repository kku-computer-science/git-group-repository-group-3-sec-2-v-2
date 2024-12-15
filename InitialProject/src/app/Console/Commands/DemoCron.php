<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Book;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DemoCron extends Command
{
    /**
     * ชื่อและลายเซ็นของคำสั่ง console
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * คำอธิบายของคำสั่ง console
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * สร้างอินสแตนซ์คำสั่งใหม่
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * รันคำสั่ง console
     *
     * @return mixed
     */
    public function handle()
    {
        // บันทึกข้อมูลเพื่อระบุว่า cron job ทำงาน
        Log::info("Cron job started");

        // ดึงข้อมูลผู้ใช้เฉพาะคนที่มี academic_ranks_en
        $users = User::whereNotNull('academic_ranks_en')->get();

        if ($users->isEmpty()) {
            Log::error("No users with academic ranks found in the database");
            return;
        }

        foreach ($users as $user) {
            // ดึงตัวอักษรแรกของชื่อและนามสกุล
            $fname = substr($user->fname_en, 0, 1); // ใช้ $user แทน $name
            $lname = $user->lname_en;

            Log::info("Processing user: $lname, $fname");

            // ทำการเรียก API เพื่อดึงข้อมูลผู้แต่ง
            $url = Http::get('https://api.elsevier.com/content/search/scopus?', [
                'query' => "AUTHOR-NAME(" . "$lname" . "," . "$fname" . ")",
                'apikey' => '6ab3c2a01c29f0e36b00c8fa1d013f83',
            ]);

            if ($url->failed()) {
                Log::error("API request failed for AUTHOR-NAME($lname, $fname)");
                continue;
            }

            $response = $url->json();

            if (!isset($response["search-results"]["entry"])) {
                Log::error("Invalid API response structure", $response);
                continue;
            }

            $content = $response["search-results"]["entry"];
            $links = $response["search-results"]["link"];

            // วนลูปเพื่อดึงหน้าผลลัพธ์ทั้งหมด
            do {
                $nextLink = null;
                foreach ($links as $link) {
                    if ($link['@ref'] == 'next') {
                        $nextLink = $link['@href'];
                        $nextResponse = Http::get($nextLink)->json();
                        $links = $nextResponse["search-results"]["link"];
                        $nextContent = $nextResponse["search-results"]["entry"];
                        $content = array_merge($content, $nextContent);
                    }
                }
            } while ($nextLink);

            // ประมวลผลแต่ละ paper ในเนื้อหา
            foreach ($content as $item) {
                if (isset($item['error'])) {
                    continue;
                }

                $paperExists = Paper::where('paper_name', '=', $item['dc:title'])->exists();

                if (!$paperExists) {
                    $paper = new Paper;
                    $paper->paper_name = $item['dc:title'];
                    $paper->paper_type = $item['prism:aggregationType'];
                    $paper->paper_subtype = $item['subtypeDescription'] ?? null;
                    $paper->paper_sourcetitle = $item['prism:publicationName'];
                    $paper->paper_url = $item['link'][2]['@href'] ?? null;
                    $paper->paper_yearpub = Carbon::parse($item['prism:coverDate'])->format('Y');
                    $paper->paper_volume = $item['prism:volume'] ?? null;
                    $paper->paper_issue = $item['prism:issueIdentifier'] ?? null;
                    $paper->paper_citation = $item['citedby-count'] ?? 0;
                    $paper->paper_page = $item['prism:pageRange'] ?? null;
                    $paper->paper_doi = $item['prism:doi'] ?? null;

                    $paper->save();
                    Log::info("Paper saved: {$paper->paper_name}");
                } else {
                    $paper = Paper::where('paper_name', '=', $item['dc:title'])->first();
                    $paper->paper_citation = $item['citedby-count'] ?? $paper->paper_citation;
                    $paper->save();
                    Log::info("Paper updated: {$paper->paper_name}");
                }
            }
        }

        Log::info("Cron job completed successfully.");
    }
}
