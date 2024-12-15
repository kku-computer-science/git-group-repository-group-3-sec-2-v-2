<?php

namespace App\Console\Commands;

use App\Models\Paper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Scopus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scopus:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update papers from Scopus API and supplement with CrossRef API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Cron job started");

        // ดึงข้อมูลผู้ใช้เฉพาะคนที่มี academic_ranks_en
        $users = User::whereNotNull('academic_ranks_en')->get();

        if ($users->isEmpty()) {
            Log::error("No users with academic ranks found in the database");
            return;
        }

        foreach ($users as $user) {
            $fname = substr($user->fname_en, 0, 1);
            $lname = $user->lname_en;

            Log::info("Processing user: $lname, $fname");

            // เรียก Scopus API เพื่อค้นหาผู้แต่ง
            $url = Http::get('https://api.elsevier.com/content/search/scopus', [
                'query' => "AUTHOR-NAME(" . "$lname" . "," . "$fname" . ")",
                'apikey' => '6ab3c2a01c29f0e36b00c8fa1d013f83',
            ]);

            if ($url->failed()) {
                Log::error("API request failed for AUTHOR-NAME($lname, $fname)");
                continue;
            }

            $response = $url->json();

            if (!isset($response["search-results"]["entry"])) {
                Log::error("Invalid API response structure for $lname, $fname");
                continue;
            }

            $content = $response["search-results"]["entry"];
            $links = $response["search-results"]["link"];

            // ดึงข้อมูลหน้าถัดไปจนกว่าจะหมด
            do {
                $nextLink = collect($links)->firstWhere('@ref', 'next')['@href'] ?? null;
                if ($nextLink) {
                    $nextResponse = Http::get($nextLink)->json();
                    $content = array_merge($content, $nextResponse["search-results"]["entry"] ?? []);
                    $links = $nextResponse["search-results"]["link"] ?? [];
                }
            } while ($nextLink);

            // ประมวลผลบทความแต่ละรายการ
            foreach ($content as $item) {
                // ตรวจสอบว่ามี DOI หรือไม่
                if (!isset($item['prism:doi'])) {
                    Log::warning("No DOI found for paper: {$item['dc:title']}");
                    continue;
                }

                $doi = $item['prism:doi'];

                // ตรวจสอบว่าบทความมีอยู่ในฐานข้อมูลหรือไม่
                $paper = Paper::where('paper_doi', $doi)->first();

                if (!$paper) {
                    $paper = new Paper();
                }

                $paper->paper_name = $item['dc:title'] ?? $paper->paper_name;
                $paper->paper_doi = $doi;
                $paper->paper_type = $item['prism:aggregationType'] ?? $paper->paper_type;
                $paper->paper_subtype = $item['subtypeDescription'] ?? $paper->paper_subtype;
                $paper->paper_sourcetitle = $item['prism:publicationName'] ?? $paper->paper_sourcetitle;
                $paper->paper_url = $item['link'][2]['@href'] ?? $paper->paper_url;
                $paper->paper_yearpub = isset($item['prism:coverDate']) ? Carbon::parse($item['prism:coverDate'])->year : $paper->paper_yearpub;
                $paper->paper_volume = $item['prism:volume'] ?? $paper->paper_volume;
                $paper->paper_issue = $item['prism:issueIdentifier'] ?? $paper->paper_issue;
                $paper->paper_citation = $item['citedby-count'] ?? $paper->paper_citation;
                $paper->paper_page = $item['prism:pageRange'] ?? $paper->paper_page;

                // ดึงข้อมูล Abstract และ Keywords จาก Scopus
                $abstractUrl = "https://api.elsevier.com/content/abstract/doi/$doi";
                $abstractResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    'X-ELS-APIKey' => '6ab3c2a01c29f0e36b00c8fa1d013f83',
                ])->get($abstractUrl);

                if ($abstractResponse->ok()) {
                    $abstractData = $abstractResponse->json();
                    $paper->abstract = $abstractData['abstracts-retrieval-response']['coredata']['dc:description'] ?? $paper->abstract;

                    if (isset($abstractData['abstracts-retrieval-response']['authkeywords']['author-keyword'])) {
                        $keywords = collect($abstractData['abstracts-retrieval-response']['authkeywords']['author-keyword'])
                            ->pluck('$')
                            ->implode(', ');
                        $paper->keyword = $keywords;
                    }
                }

                // หาก Scopus ไม่มีข้อมูลที่จำเป็น ดึงจาก CrossRef API
                if (!$paper->abstract || !$paper->keyword) {
                    $crossrefResponse = Http::get("https://api.crossref.org/works/$doi");

                    if ($crossrefResponse->ok()) {
                        $crossrefData = $crossrefResponse->json()['message'];
                        $paper->abstract = $paper->abstract ?? strip_tags($crossrefData['abstract'] ?? null);
                    }
                }

                // ฟิลด์ที่ไม่มีข้อมูลใน API
                $paper->publication = $item['dc:publisher'] ?? $paper->publication;
                $paper->paper_funder = null; // ยังไม่มีข้อมูลผู้ให้ทุนใน API
                $paper->reference_number = null; // หากต้องการเพิ่ม Reference Number

                $paper->save();
                Log::info("Paper saved or updated: {$paper->paper_name}");
            }
        }

        Log::info("Cron job completed successfully.");
    }
}
