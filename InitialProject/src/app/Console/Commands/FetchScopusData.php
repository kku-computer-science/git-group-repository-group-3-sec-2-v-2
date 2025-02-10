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
    protected $description = 'Fetch Scopus search data, enrich with abstract details, and save to a JSON file with the desired format';

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
            ])->get('https://api.elsevier.com/content/search/scopus?query=AUTHOR-NAME(Wongthanavasu,s)');

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
            $counter = 1; // ใช้สำหรับจำลอง id

            // 2. สำหรับแต่ละงานวิจัยให้เติมข้อมูลเพิ่มเติมจาก Abstract API
            foreach ($papers as $paper) {
                // ดึง Scopus ID จากผลการค้นหา (ตัวอย่าง "SCOPUS_ID:85211026637")
                $raw_scopus_id = $paper['dc:identifier'] ?? '';
                $numeric_scopus_id = str_replace('SCOPUS_ID:', '', $raw_scopus_id);

                // สร้าง URL สำหรับดึงรายละเอียดเพิ่มเติม (Abstract API)
                $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$numeric_scopus_id}";

                // กำหนดค่าจากผลการค้นหาที่ใช้เป็น fallback
                $paper_name   = $paper['dc:title'] ?? null;
                $abstract     = null;
                $paper_funder = null;

                // ดึงข้อมูลรายละเอียด (Abstract API)
                $detailResponse = Http::withHeaders([
                    'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                    'Accept'      => 'application/json',
                ])->get($detailUrl);

                if ($detailResponse->successful()) {
                    $paperDetails = $detailResponse->json('abstracts-retrieval-response.item');

                    // ถ้ามี citation-title ให้ใช้เป็น paper_name
                    if (isset($paperDetails['bibrecord']['head']['citation-title'])) {
                        $paper_name = $paperDetails['bibrecord']['head']['citation-title'];
                    }

                    // abstract จากรายละเอียด (ถ้ามี)
                    if (isset($paperDetails['bibrecord']['head']['abstracts'])) {
                        $abstract = $paperDetails['bibrecord']['head']['abstracts'];
                    }

                    // paper_funder จาก funding-text (ถ้ามี)
                    if (isset($paperDetails['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                        $paper_funder = $paperDetails['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                    }
                } else {
                    Log::warning("Failed to fetch details for Scopus ID: {$numeric_scopus_id}. Using fallback data.");
                }

                // กำหนดค่า paper_url
                // หากผลการค้นหามี link ที่เกี่ยวข้อง (โดยสมมุติว่า link[0] ให้ค่า URL ของ inward record)
                $paper_url = $paper['link'][0]['@href'] ?? $detailUrl;

                // จัดเตรียมข้อมูลตามลำดับฟิลด์ที่ต้องการ
                $data = [
                    'id'                => $counter++, // จำลอง id ด้วย counter
                    'paper_name'        => $paper_name,
                    'abstract'          => $abstract,
                    'paper_type'        => $paper['prism:aggregationType'] ?? 'Journal',        // จาก "prism:aggregationType"
                    'paper_subtype'     => $paper['subtype'] ?? 'ar',                              // จาก "subtype"
                    'paper_sourcetitle' => $paper['subtypeDescription'] ?? 'Article',              // จาก "subtypeDescription"
                    // เก็บ keywords เป็น JSON string (ตามตัวอย่าง)
                    'keyword'           => isset($paper['author-keywords']) ? json_encode($paper['author-keywords'], JSON_UNESCAPED_UNICODE) : '',
                    'paper_url'         => $paper_url,
                    'publication'       => $paper['prism:publicationName'] ?? null,                // จาก "prism:publicationName"
                    'paper_yearpub'     => isset($paper['prism:coverDate']) ? substr($paper['prism:coverDate'], 0, 4) : null,
                    'paper_volume'      => $paper['prism:volume'] ?? null,
                    'paper_issue'       => $paper['prism:issueIdentifier'] ?? '-',
                    'paper_citation'    => $paper['citedby-count'] ?? 0,
                    'paper_page'        => $paper['prism:pageRange'] ?? '-',
                    'paper_doi'         => $paper['prism:doi'] ?? null,
                    'paper_funder'      => $paper_funder,
                    'reference_number'  => null, // ตามตัวอย่าง ให้เก็บเป็น NULL
                ];

                $jsonData[] = $data;
            }

            // 3. บันทึกข้อมูลทั้งหมดลงในไฟล์ JSON (ไม่รวม created_at/updated_at ตามตัวอย่าง)
            $filename = 'scopus_data_' . now()->format('Y-m-d_H-i-s') . '.json';
            Storage::disk('local')->put('scopus/' . $filename, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info("Scopus data enriched and saved to JSON file: {$filename}");
            $this->info("Data saved successfully to {$filename}");
        } catch (\Exception $e) {
            Log::error("Error fetching/enriching Scopus data: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
        }
    }
}
