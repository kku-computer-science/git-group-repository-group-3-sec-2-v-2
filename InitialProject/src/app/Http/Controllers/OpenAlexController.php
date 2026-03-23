<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAlexController extends Controller
{
    /**
     * Sync missing paper data (Abstract, Citations, Keywords) using OpenAlex API via DOIs.
     */
    public function syncData(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->with('error', 'User not authenticated.');
        }

        // Get papers based on role (similar to PaperController@index)
        $query = Paper::whereNotNull('paper_doi')->where('paper_doi', '!=', '');

        if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
            $query->whereHas('teacher', function ($q) use ($user) {
                $q->where('users.id', '=', $user->id);
            });
        }

        $papers = $query->get();
        $updatedCount = 0;
        $updatedTitles = [];

        foreach ($papers as $paper) {
            try {
                // Formatting DOI properly
                $doi = trim($paper->paper_doi);
                if (!str_starts_with($doi, '10.')) {
                    continue; // Not a valid standard DOI
                }
                
                $openAlexUrl = "https://api.openalex.org/works/https://doi.org/{$doi}";
                
                // Add polite mailto header to get higher limits
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'mailto:admin@cpkkuhost.com' 
                ])->get($openAlexUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    $hasUpdates = false;

                    // 1. Process Abstract
                    if (empty($paper->abstract) && isset($data['abstract_inverted_index'])) {
                        $words = [];
                        foreach ($data['abstract_inverted_index'] as $word => $positions) {
                            foreach ($positions as $pos) {
                                $words[$pos] = $word;
                            }
                        }
                        ksort($words);
                        $paper->abstract = implode(' ', $words);
                        $hasUpdates = true;
                    }

                    // 2. Process Citations (update if OpenAlex has more citations)
                    $currentCitations = (int)$paper->paper_citation;
                    $newCitations = $data['cited_by_count'] ?? 0;
                    if ($newCitations > $currentCitations) {
                        $paper->paper_citation = $newCitations;
                        $hasUpdates = true;
                    }

                    // 3. Process Keywords (if missing)
                    if (empty($paper->keyword) && !empty($data['concepts'])) {
                        $keywordsArray = [];
                        foreach ($data['concepts'] as $concept) {
                            // Take top concepts (score > 0.3) to avoid spam
                            if (($concept['score'] ?? 0) > 0.3) {
                                $keywordsArray[] = ['$' => $concept['display_name']];
                            }
                        }
                        
                        if (!empty($keywordsArray)) {
                            // Take up to 10 keywords
                            $keywordsArray = array_slice($keywordsArray, 0, 10);
                            $paper->keyword = json_encode($keywordsArray, JSON_UNESCAPED_UNICODE);
                            $hasUpdates = true;
                        }
                    }

                    if ($hasUpdates) {
                        $paper->save();
                        $updatedCount++;
                        $updatedTitles[] = $paper->paper_name;
                    }
                }
            } catch (Exception $e) {
                // Skip on error
                continue;
            }
        }

        if ($updatedCount > 0) {
            $titlesStr = implode("\n- ", array_slice($updatedTitles, 0, 10));
            if (count($updatedTitles) > 10) {
                $titlesStr .= "\n...and " . (count($updatedTitles) - 10) . " more.";
            }
            return redirect()->back()->with('insertedPapers', ["Successfully updated {$updatedCount} papers with missing metadata (Abstracts, Citations, Keywords)!\n\nUpdated Papers:\n- {$titlesStr}"]);
        }

        return redirect()->back()->with('info', "No new missing data was found or updated for your DOIs.");
    }
}
