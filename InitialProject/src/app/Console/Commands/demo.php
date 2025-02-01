<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DemoCron extends Command
{
    protected $signature = 'demo:cron';
    protected $description = 'Update Scopus papers data automatically';
    private const SCOPUS_API_KEY = '6ab3c2a01c29f0e36b00c8fa1d013f83';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            Log::info("Starting Scopus data update cron job");

            // ดึงข้อมูล user
            //$users = User::role(['teacher', 'student'])->get();
            $users = User::find(16);
            
            if (!$users) {
                Log::warning("No users found to process");
                return;
            }

            foreach ($users as $user) {
                $this->processUser($user);
            }

            Log::info("Completed Scopus data update cron job");

        } catch (Exception $e) {
            Log::error("Error in Scopus update cron: " . $e->getMessage());
        }
    }

    private function processUser($user)
    {
        try {
            $fname = substr($user['fname_en'], 0, 1);
            $lname = $user['lname_en'];
            
            Log::info("Processing user: {$lname}, {$fname}");

            // เรียก Scopus API
            $response = Http::get('https://api.elsevier.com/content/search/scopus?', [
                'query' => "AUTHOR-NAME($lname,$fname)",
                'apikey' => self::SCOPUS_API_KEY,
            ]);

            if (!$response->successful()) {
                Log::error("Failed to fetch data from Scopus for user: {$user['id']}");
                return;
            }

            $data = $response->json();
            
            if (empty($data["search-results"]["entry"])) {
                Log::info("No papers found for user: {$user['id']}");
                return;
            }

            $content = $data["search-results"]["entry"];
            $links = $data["search-results"]["link"];

            // ดึงข้อมูลหน้าถัดไป
            $content = $this->fetchAllPages($content, $links);

            // ประมวลผลแต่ละ paper
            foreach ($content as $item) {
                if (!array_key_exists('error', $item)) {
                    $this->processPaper($item, $user['id']);
                }
            }

        } catch (Exception $e) {
            Log::error("Error processing user {$user['id']}: " . $e->getMessage());
        }
    }

    private function fetchAllPages($content, $links)
    {
        try {
            do {
                $ref = 'prev';
                foreach ($links as $link) {
                    if ($link['@ref'] == 'next') {
                        $response = Http::get($link['@href']);
                        if ($response->successful()) {
                            $nextData = $response->json();
                            $links = $nextData["search-results"]["link"];
                            foreach ($nextData["search-results"]["entry"] as $item) {
                                array_push($content, $item);
                            }
                        }
                    }
                }
            } while ($ref != 'prev');

            return $content;

        } catch (Exception $e) {
            Log::error("Error fetching additional pages: " . $e->getMessage());
            return $content;
        }
    }

    private function processPaper($item, $userId)
    {
        try {
            $existingPaper = Paper::where('paper_name', '=', $item['dc:title'])->first();

            if ($existingPaper) {
                // อัพเดท citation count
                $existingPaper->paper_citation = $item['citedby-count'];
                $existingPaper->update();
                Log::info("Updated citation count for paper: {$existingPaper->id}");
                return;
            }

            // สร้าง paper ใหม่
            $scoid = explode(":", $item['dc:identifier'])[1];
            $abstractData = $this->fetchAbstractData($scoid);
            
            if (!$abstractData) {
                return;
            }

            $paper = $this->createNewPaper($item, $abstractData);
            $this->processAuthors($abstractData['abstracts-retrieval-response']['authors']['author'], $paper);

            Log::info("Created new paper: {$paper->id}");

        } catch (Exception $e) {
            Log::error("Error processing paper: " . $e->getMessage());
        }
    }

    private function fetchAbstractData($scopusId)
    {
        try {
            $response = Http::get("https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}?filed=authors&apiKey=" . self::SCOPUS_API_KEY . "&httpAccept=application%2Fjson");
            
            if (!$response->successful()) {
                Log::error("Failed to fetch abstract data for scopus ID: {$scopusId}");
                return null;
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Error fetching abstract data: " . $e->getMessage());
            return null;
        }
    }

    private function createNewPaper($item, $abstractData)
    {
        $paper = new Paper;
        
        // ข้อมูลพื้นฐาน
        $paper->paper_name = $item['dc:title'];
        $paper->paper_type = $item['prism:aggregationType'];
        $paper->paper_subtype = $item['subtypeDescription'];
        $paper->paper_sourcetitle = $item['prism:publicationName'];
        $paper->paper_url = $item['link'][2]['@href'];
        $paper->paper_yearpub = Carbon::parse($item['prism:coverDate'])->format('Y');
        $paper->paper_citation = $item['citedby-count'];
        $paper->paper_page = $item['prism:pageRange'];

        // ข้อมูลเพิ่มเติม (ถ้ามี)
        $paper->paper_volume = $item['prism:volume'] ?? null;
        $paper->paper_issue = $item['prism:issueIdentifier'] ?? null;
        $paper->paper_doi = $item['prism:doi'] ?? null;

        // ข้อมูลจาก abstract
        if (!empty($abstractData['abstracts-retrieval-response']['item'])) {
            $metadata = $abstractData['abstracts-retrieval-response']['item'];
            
            // Funding information
            if (!empty($metadata['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                $paper->paper_funder = json_encode($metadata['xocs:meta']['xocs:funding-list']['xocs:funding-text']);
            }

            // Abstract
            if (!empty($metadata['bibrecord']['head']['abstracts'])) {
                $paper->abstract = $metadata['bibrecord']['head']['abstracts'];
            }

            // Keywords
            if (!empty($metadata['bibrecord']['head']['citation-info']['author-keywords']['author-keyword'])) {
                $paper->keyword = json_encode($metadata['bibrecord']['head']['citation-info']['author-keywords']['author-keyword']);
            }
        }

        $paper->save();

        // Attach source
        $source = Source_data::findOrFail(1);
        $paper->source()->sync($source);

        return $paper;
    }

    private function processAuthors($authors, $paper)
    {
        $position = 1;
        $totalAuthors = count($authors);

        foreach ($authors as $authorData) {
            try {
                $givenName = $authorData['ce:given-name'] ?? $authorData['preferred-name']['ce:given-name'];
                $surname = $authorData['ce:surname'];

                $user = User::where([
                    ['fname_en', '=', $givenName],
                    ['lname_en', '=', $surname]
                ])->orWhere([
                    [DB::raw("concat(left(fname_en,1),'.')"), '=', $givenName],
                    ['lname_en', '=', $surname]
                ])->first();

                if ($user) {
                    // ถ้าเป็น user ในระบบ
                    $authorType = $this->getAuthorType($position, $totalAuthors);
                    $paper->teacher()->attach($user, ['author_type' => $authorType]);
                } else {
                    // ถ้าไม่ใช่ user ในระบบ
                    $author = Author::firstOrCreate(
                        [
                            'author_fname' => $givenName,
                            'author_lname' => $surname
                        ]
                    );
                    
                    $authorType = $this->getAuthorType($position, $totalAuthors);
                    $paper->author()->attach($author, ['author_type' => $authorType]);
                }

                $position++;

            } catch (Exception $e) {
                Log::error("Error processing author: " . $e->getMessage());
            }
        }
    }

    private function getAuthorType($position, $total)
    {
        if ($position === 1) return 1; // First author
        if ($position === $total) return 3; // Last author
        return 2; // Middle author
    }
}