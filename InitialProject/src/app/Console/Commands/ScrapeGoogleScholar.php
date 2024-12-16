<?php

namespace App\Console\Commands;

use App\Models\Paper;
use Illuminate\Console\Command;
use Goutte\Client;
use Illuminate\Support\Facades\Log;

class ScrapeGoogleScholar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:google-scholar {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape authors and papers from Google Scholar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->argument('query');
        $baseUrl = "https://scholar.google.com";

        Log::info("Starting to scrape authors for query: $query");

        $client = new Client();

        // URL ค้นหานักวิจัย
        $searchUrl = "$baseUrl/citations?view_op=search_authors&mauthors=" . urlencode($query);

        // ดึงข้อมูลจากหน้าค้นหานักวิจัย
        $crawler = $client->request('GET', $searchUrl);

        $crawler->filter('.gsc_1usr')->each(function ($node) use ($baseUrl, $client) {
            try {
                // ดึงข้อมูลโปรไฟล์นักวิจัย
                $authorName = $node->filter('.gs_ai_name a')->text('');
                $profileUrl = $baseUrl . $node->filter('.gs_ai_name a')->attr('href');
                $affiliation = $node->filter('.gs_ai_aff')->text('');
                $email = $node->filter('.gs_ai_eml')->text('');
                $citationCount = $node->filter('.gs_ai_cby')->text('');
                $interests = $node->filter('.gs_ai_int a')->each(function ($interestNode) {
                    return $interestNode->text('');
                });

                Log::info("Scraping profile: $authorName");

                // เข้าไปยังหน้าโปรไฟล์นักวิจัย
                $profileCrawler = $client->request('GET', $profileUrl);

                // ดึงข้อมูล Paper จากหน้าโปรไฟล์
                $profileCrawler->filter('.gsc_a_tr')->each(function ($paperNode) use ($authorName) {
                    try {
                        $paperTitle = $paperNode->filter('.gsc_a_t a')->text('');
                        $paperUrl = $paperNode->filter('.gsc_a_t a')->attr('href') ?? null;
                        $paperAuthors = $paperNode->filter('.gsc_a_t .gs_gray')->eq(0)->text('');
                        $publicationSource = $paperNode->filter('.gsc_a_t .gs_gray')->eq(1)->text('');
                        $year = $paperNode->filter('.gsc_a_y span')->text('');

                        // บันทึก Paper ลงในฐานข้อมูล
                        $paper = new Paper();
                        $paper->paper_name = $paperTitle;
                        $paper->paper_url = $paperUrl ? "https://scholar.google.com" . $paperUrl : null;
                        $paper->paper_yearpub = $year;
                        $paper->paper_sourcetitle = $publicationSource;
                        $paper->save();

                        Log::info("Saved paper: $paperTitle");
                    } catch (\Exception $e) {
                        Log::error("Error scraping paper for $authorName: " . $e->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error("Error scraping author: " . $e->getMessage());
            }
        });

        Log::info("Scraping process completed.");
    }
}
