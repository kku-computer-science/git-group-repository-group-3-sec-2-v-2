<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Paper;
use Illuminate\Support\Facades\Http;
use Exception;

class UpdatePaperCitationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'papers:update-citations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily cron job to update paper citation counts via OpenAlex API using DOI';

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
        $this->info('Starting daily paper citation update...');

        // Retrieve all papers with a valid DOI
        $papers = Paper::whereNotNull('paper_doi')->where('paper_doi', '!=', '')->get();
        $updatedCount = 0;

        foreach ($papers as $paper) {
            try {
                $doiClean = trim($paper->paper_doi);
                if (str_starts_with($doiClean, '10.')) {
                    $openAlexUrl = "https://api.openalex.org/works/https://doi.org/{$doiClean}";
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'mailto:admin@cpkkuhost.com' 
                    ])->timeout(10)->get($openAlexUrl);

                    if ($response->successful()) {
                        $oaData = $response->json();
                        $newCitations = $oaData['cited_by_count'] ?? 0;
                        $currentCitations = (int) $paper->paper_citation;

                        if ($newCitations > $currentCitations) {
                            $paper->paper_citation = $newCitations;
                            $paper->save();
                            $updatedCount++;
                            $this->info("Updated '{$paper->paper_name}': Citations upgraded from {$currentCitations} to {$newCitations}");
                        }
                    }
                }
            } catch (Exception $e) {
                // Silently skip on error to prevent stopping the entire job
                $this->error("Failed to fetch data for DOI {$paper->paper_doi}. Check connection or limit.");
            }
        }

        $this->info("Citation update complete. Total papers updated: {$updatedCount}");
        return 0;
    }
}
