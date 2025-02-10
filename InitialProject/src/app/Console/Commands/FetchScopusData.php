<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchScopusData extends Command
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
    protected $description = 'Fetch Scopus search data, enrich with abstract details, and save to a JSON file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // 1. ดึงข้อมูลจาก Scopus Search API
            $searchResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'      => 'application/json',
            ])->get('https://api.elsevier.com/content/search/scopus?query=AUTHOR-NAME(janyoi,p)');

            if (!$searchResponse->successful()) {
                Log::error("Failed to fetch Scopus search data. Status: " . $searchResponse->status());
                $this->error("Failed to fetch Scopus search data.");
                return 1;
            }

            $papers = $searchResponse->json('search-results.entry');
            if (!$papers || !is_array($papers)) {
                Log::error("No papers found in the Scopus search results.");
                $this->error("No papers found in the search results.");
                return 1;
            }

            $jsonData = [];

            // 2. สำหรับแต่ละงานวิจัย ให้ดึงรายละเอียดเพิ่มเติมจาก paper_url
            foreach ($papers as $paper) {
                // ดึง Scopus ID จากผลลัพธ์ (ตัวอย่าง "SCOPUS_ID:85211026637")
                $raw_scopus_id = $paper['dc:identifier'] ?? '';
                // เอาเฉพาะหมายเลขโดยลบ "SCOPUS_ID:" ออก
                $numeric_scopus_id = str_replace('SCOPUS_ID:', '', $raw_scopus_id);

                // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
                $paper_url = "https://api.elsevier.com/content/abstract/scopus_id/{$numeric_scopus_id}";

                // กำหนดค่าตั้งต้นจาก Search API
                $paper_name   = $paper['dc:title'] ?? null;
                $abstract     = null;
                $publication  = $paper['dc:creator'] ?? null;
                $paper_funder = null;

                // ดึงรายละเอียดเพิ่มเติมจาก paper_url
                $detailResponse = Http::withHeaders([
                    'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                    'Accept'      => 'application/json',
                ])->get($paper_url);

                if ($detailResponse->successful()) {
                    // ข้อมูลรายละเอียดอยู่ใน abstracts-retrieval-response.item
                    $paperDetails = $detailResponse->json('abstracts-retrieval-response.item');

                    // เติม paper_name หากมีในรายละเอียด (citation-title)
                    if (isset($paperDetails['bibrecord']['head']['citation-title'])) {
                        $paper_name = $paperDetails['bibrecord']['head']['citation-title'];
                    }

                    // เติม abstract จากรายละเอียด (abstracts)
                    if (isset($paperDetails['bibrecord']['head']['abstracts'])) {
                        $abstract = $paperDetails['bibrecord']['head']['abstracts'];
                    }

                    // เติม publication โดยดึงชื่อผู้แต่งคนแรกจาก author-group
                    if (isset($paperDetails['bibrecord']['head']['author-group']['author'])) {
                        $authors = $paperDetails['bibrecord']['head']['author-group']['author'];
                        if (is_array($authors)) {
                            // ตรวจสอบกรณีเป็น indexed array หรือ associative array
                            if (isset($authors[0])) {
                                $firstAuthor = $authors[0];
                            } else {
                                $firstAuthor = $authors;
                            }
                            $publication = $firstAuthor['preferred-name']['ce:indexed-name'] ?? $publication;
                        }
                    }

                    // เติมข้อมูล paper_funder หากมี (จาก funding-text)
                    if (isset($paperDetails['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                        $paper_funder = $paperDetails['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                    }
                } else {
                    Log::warning("Failed to fetch details for Scopus ID: {$numeric_scopus_id}. Using fallback data from search result.");
                }

                // 3. จัดเตรียมข้อมูลให้ตรงกับ Schema ที่ต้องการ
                $data = [
                    'paper_name'         => $paper_name,
                    'abstract'           => $abstract,
                    'paper_type'         => $paper['subtype'] ?? null,
                    'paper_subtype'      => $paper['subtype'] ?? null,
                    'paper_sourcetitle'  => $paper['prism:publicationName'] ?? null,
                    'keyword'            => isset($paper['author-keywords'])
                        ? (is_array($paper['author-keywords']) ? implode(', ', $paper['author-keywords']) : $paper['author-keywords'])
                        : '',
                    'paper_url'          => $paper_url,
                    'publication'        => $publication,
                    'paper_yearpub'      => isset($paper['prism:coverDate']) ? substr($paper['prism:coverDate'], 0, 4) : null,
                    'paper_volume'       => $paper['prism:volume'] ?? null,
                    'paper_issue'        => $paper['prism:issueIdentifier'] ?? null,
                    'paper_citation'     => $paper['citedby-count'] ?? null,
                    'paper_page'         => $paper['prism:pageRange'] ?? null,
                    'paper_doi'          => $paper['prism:doi'] ?? null,
                    'paper_funder'       => $paper_funder,
                    'reference_number'   => $raw_scopus_id,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                $jsonData[] = $data;
            }

            // 4. บันทึกข้อมูลทั้งหมดลงในไฟล์ JSON โดยใช้ timestamp ในชื่อไฟล์
            $filename = 'scopus_data_' . now()->format('Y-m-d_H-i-s') . '.json';
            Storage::disk('local')->put('scopus/' . $filename, json_encode($jsonData, JSON_PRETTY_PRINT));

            Log::info("Scopus data enriched and saved to JSON file: {$filename}");
            $this->info("Data saved successfully to {$filename}");
        } catch (\Exception $e) {
            Log::error("Error fetching/enriching Scopus data: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
        }
    }
}
