<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Paper;
use App\Models\Author;
use App\Models\Source_data;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ScopusFetchCommand extends Command
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
    protected $description = 'Fetch data from Scopus for all users with academic ranks (is_research=1).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Fetch all teachers (or users with is_research=1)
        $users = User::where('is_research', 1)->orWhereHas('roles', function($q){ $q->where('name', 'teacher'); })->get();
        if ($users->isEmpty()) {
            $this->info('No users found.');
            return 0;
        }

        foreach ($users as $user) {
            $completePapers = [];
            $incompletePapers = [];

            // ---------------------------------------------------------------------
            //                           1. Scopus API Section
            // ---------------------------------------------------------------------

            $firstLetter = substr($user->fname_en, 0, 1);
            $lname       = $user->lname_en;
            $searchQuery = "AUTHOR-NAME({$lname},{$firstLetter})";

            $searchResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get("https://api.elsevier.com/content/search/scopus", [
                'query' => $searchQuery,
            ]);

            if (!$searchResponse->successful()) {
                Log::error("Failed to fetch Scopus search data for user_id: {$user->id}");
                continue;
            }

            $entries = $searchResponse->json('search-results.entry');
            if (!$entries || !is_array($entries)) {
                continue;
            }

            foreach ($entries as $item) {
                try {
                    $scopusPaperName = $item['dc:title'] ?? null;
                    if (!$scopusPaperName) continue;

                    $existingPaper = Paper::where('paper_name', $scopusPaperName)->first();
                    if ($existingPaper) {
                        $paper_citation = $item['citedby-count'] ?? 0;
                        if ($paper_citation > $existingPaper->paper_citation) {
                           $existingPaper->paper_citation = $paper_citation;
                           $existingPaper->save();
                        }
                        $exists = $existingPaper->teacher()->where('user_id', $user->id)->exists();
                        if (!$exists) $existingPaper->teacher()->attach($user->id, ['author_type' => 2]);
                        continue;
                    }

                    $rawScopusId = $item['dc:identifier'] ?? '';
                    $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
                    $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

                    $detailResponse = Http::withHeaders([
                        'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                        'Accept'       => 'application/json',
                    ])->get($detailUrl);

                    $paper_name   = $scopusPaperName;
                    $abstract     = null;
                    $paper_funder = null;
                    $detailData   = [];

                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json('abstracts-retrieval-response.item');
                        if (isset($detailData['bibrecord']['head']['citation-title'])) {
                            $paper_name = $detailData['bibrecord']['head']['citation-title'];
                        }
                        if (isset($detailData['bibrecord']['head']['abstracts'])) {
                            $abs = $detailData['bibrecord']['head']['abstracts'];
                            $abstract = is_array($abs) ? json_encode($abs, JSON_UNESCAPED_UNICODE) : $abs;
                        }
                        if (isset($detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                            $funderRaw = $detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                            $paper_funder = is_array($funderRaw) ? json_encode($funderRaw, JSON_UNESCAPED_UNICODE) : $funderRaw;
                        }
                    }

                    $paper_url = $detailUrl;
                    if (!empty($item['link']) && is_array($item['link'])) {
                        foreach ($item['link'] as $linkObj) {
                            if (isset($linkObj['@ref']) && $linkObj['@ref'] === 'scopus') {
                                $paper_url = $linkObj['@href'] ?? $detailUrl;
                                break;
                            }
                        }
                    }

                    $coverDate = $item['prism:coverDate'] ?? null;
                    $paper_yearpub = $coverDate ? substr($coverDate, 0, 4) : null;
                    $subtype = $item['subtype'] ?? 'ar';
                    if ($subtype === 'ar') $subtype = 'Article';

                    $paper_doi = $item['prism:doi'] ?? null;

                    // --------------- OPENALEX INTEGRATION -----------------
                    $openAlexAuthorships = null;
                    if ($paper_doi) {
                        try {
                            $doiClean = trim($paper_doi);
                            if (str_starts_with($doiClean, '10.')) {
                                $openAlexUrl = "https://api.openalex.org/works/https://doi.org/{$doiClean}";
                                $oaResponse = Http::withHeaders(['Accept' => 'application/json', 'User-Agent' => 'mailto:admin@cpkkuhost.com'])->timeout(10)->get($openAlexUrl);
                                if ($oaResponse->successful()) {
                                    $oaData = $oaResponse->json();
                                    if (empty($abstract) && isset($oaData['abstract_inverted_index'])) {
                                        $words = [];
                                        foreach ($oaData['abstract_inverted_index'] as $word => $positions) {
                                            foreach ($positions as $pos) $words[$pos] = $word;
                                        }
                                        ksort($words);
                                        $abstract = implode(' ', $words);
                                    }
                                    $newCitations = $oaData['cited_by_count'] ?? 0;
                                    $currentCitations = $item['citedby-count'] ?? 0;
                                    $item['citedby-count'] = max($newCitations, $currentCitations);

                                    if (empty($item['author-keywords']) && !empty($oaData['concepts'])) {
                                        $keywordsArray = [];
                                        foreach ($oaData['concepts'] as $concept) {
                                            if (($concept['score'] ?? 0) > 0.3) {
                                                $keywordsArray[] = $concept['display_name'];
                                            }
                                        }
                                        if (!empty($keywordsArray)) {
                                            $item['author-keywords'] = implode(', ', array_slice($keywordsArray, 0, 10));
                                        }
                                    }
                                    if (!empty($oaData['authorships'])) $openAlexAuthorships = $oaData['authorships'];
                                }
                            }
                        } catch (\Exception $e) {}
                    }
                    // ------------------------------------------------------

                    $kwRaw = $item['author-keywords'] ?? null;
                    $kwStr = null;
                    if ($kwRaw) {
                        if (is_array($kwRaw)) {
                            $kws = [];
                            foreach ($kwRaw as $k) {
                                if (is_array($k) && isset($k['$'])) {
                                    $kws[] = $k['$'];
                                } elseif (is_string($k)) {
                                    $kws[] = $k;
                                }
                            }
                            $kwStr = implode(', ', $kws);
                        } else {
                            $kwStr = str_replace(' | ', ', ', $kwRaw);
                        }
                    }

                    $paper = new Paper;
                    $paper->paper_name        = $paper_name;
                    $paper->abstract          = $abstract;
                    $paper->paper_type        = $item['prism:aggregationType'] ?? 'Journal';
                    $paper->paper_subtype     = $subtype;
                    $subtypeDesc = $item['subtypeDescription'] ?? 'Article';
                    if (is_array($subtypeDesc)) $subtypeDesc = json_encode($subtypeDesc, JSON_UNESCAPED_UNICODE);
                    $paper->paper_sourcetitle = $subtypeDesc;
                    $paper->keyword           = $kwStr;
                    $paper->paper_url         = $paper_url;
                    $paper->publication       = $item['prism:publicationName'] ?? null;
                    $paper->paper_yearpub     = $paper_yearpub;
                    $paper->paper_volume      = $item['prism:volume'] ?? null;
                    $paper->paper_issue       = $item['prism:issueIdentifier'] ?? '-';
                    $paper->paper_citation    = $item['citedby-count'] ?? 0;
                    $paper->paper_page        = $item['prism:pageRange'] ?? '-';
                    $paper->paper_doi         = $paper_doi;
                    $paper->paper_funder      = $paper_funder;
                    $paper->reference_number  = null;
                    $paper->save();

                    $missingFields = [];
                    if (empty($paper->abstract)) $missingFields[] = 'Abstract';
                    if (empty($paper->keyword)) $missingFields[] = 'Keywords';
                    if (empty($paper->paper_doi)) $missingFields[] = 'DOI';

                    if (empty($missingFields)) {
                        $completePapers[] = $paper->paper_name;
                    } else {
                        $incompletePapers[] = $paper->paper_name;
                    }

                    $source = Source_data::find(1);
                    if ($source) $paper->source()->sync([$source->id]);

                    if ($openAlexAuthorships) {
                        foreach ($openAlexAuthorships as $oaAuthor) {
                            $rawName = $oaAuthor['raw_author_name'] ?? $oaAuthor['author']['display_name'] ?? '';
                            $parts = explode(' ', trim($rawName));
                            $surname = array_pop($parts);
                            $givenName = implode(' ', $parts);

                            $posStr = $oaAuthor['author_position'] ?? 'middle';
                            $author_type = ($posStr === 'first') ? 1 : (($posStr === 'last') ? 3 : 2);

                            $isSameUser = false;
                            if (stripos($rawName, $user->fname_en) !== false && stripos($rawName, $user->lname_en) !== false) {
                                $isKKU = false;
                                if (!empty($oaAuthor['institutions'])) {
                                    foreach ($oaAuthor['institutions'] as $inst) {
                                        if (stripos($inst['display_name'] ?? '', 'Khon Kaen') !== false) {
                                            $isKKU = true; break;
                                        }
                                    }
                                }
                                if ($isKKU) $isSameUser = true;
                            }

                            if ($isSameUser) {
                                $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                            } else {
                                $existingUser = User::whereRaw("LOWER(CONCAT(fname_en, ' ', lname_en)) = ?", [strtolower($rawName)])
                                    ->orWhere(function($query) use ($givenName, $surname) {
                                        $query->where('fname_en', 'LIKE', "%{$givenName}%")->where('lname_en', 'LIKE', "%{$surname}%");
                                    })->first();

                                if ($existingUser) {
                                    $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                                } else {
                                    $existingAuthor = Author::whereRaw("LOWER(CONCAT(author_fname, ' ', author_lname)) = ?", [strtolower($rawName)])
                                        ->orWhere(function($query) use ($givenName, $surname) {
                                            $query->where('author_fname', 'LIKE', "%{$givenName}%")->where('author_lname', 'LIKE', "%{$surname}%");
                                        })->first();
                                    if (!$existingAuthor) {
                                        $newAuthor = new Author;
                                        $newAuthor->author_fname = $givenName ?: $rawName;
                                        $newAuthor->author_lname = $surname ?: '-';
                                        $newAuthor->save();
                                        $paper->author()->attach($newAuthor->id, ['author_type' => $author_type]);
                                    } else {
                                        $paper->author()->attach($existingAuthor->id, ['author_type' => $author_type]);
                                    }
                                }
                            }
                        }
                    } else {
                        $authorsData = $detailData['bibrecord']['head']['author-group']['author'] ?? $detailResponse->json('abstracts-retrieval-response.authors.author');
                        if ($authorsData && !is_array($authorsData)) $authorsData = [$authorsData];
                        if ($authorsData && is_array($authorsData)) {
                            $totalAuthors = count($authorsData);
                            $x = 1;
                            foreach ($authorsData as $authorItem) {
                                $givenName = $authorItem['ce:given-name'] ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                                $surname   = $authorItem['ce:surname'] ?? ($authorItem['preferred-name']['ce:surname'] ?? '');
                                $author_type = ($x === 1) ? 1 : (($x === $totalAuthors) ? 3 : 2);
    
                                $isSameUser = false;
                                if (strcasecmp($givenName, $user->fname_en) === 0 && strcasecmp($surname, $user->lname_en) === 0 && strtolower(substr($givenName, 0, 1)) === strtolower(substr($user->fname_en, 0, 1))) {
                                    if (!empty($authorItem['affiliation']) && is_array($authorItem['affiliation'])) {
                                        foreach ($authorItem['affiliation'] as $aff) {
                                            $affName = $aff['affiliation-name'] ?? '';
                                            if (stripos($affName, 'Khon Kaen') !== false) {
                                                $isSameUser = true; break;
                                            }
                                        }
                                    }
                                }
    
                                if ($isSameUser) {
                                    $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                                } else {
                                    $existingUser = User::where('fname_en', $givenName)->where('lname_en', $surname)->first();
                                    if ($existingUser) {
                                        $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                                    } else {
                                        $existingAuthor = Author::where('author_fname', $givenName)->where('author_lname', $surname)->first();
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
                                }
                                $x++;
                            }
                        }
                    }

                    $exists = $paper->teacher()->where('user_id', $user->id)->exists();
                    if (!$exists) $paper->teacher()->attach($user->id, ['author_type' => 2]);
                } catch (\Exception $e) {}
            }

            // ---------------------------------------------------------------------
            //                           2. OpenAlex API Fetch
            // ---------------------------------------------------------------------
            if (!empty($user->orcid)) {
                $this->fetchFromOpenAlexByOrcid($user, $completePapers, $incompletePapers);
            }

            $countInserted = count($completePapers) + count($incompletePapers);
            if ($countInserted > 0) {
                $this->info("User {$user->fname_en} imported {$countInserted} papers.");
                Log::info("User {$user->id} imported {$countInserted} papers from Scopus via Auto-Cron.");
            }
        }

        return 0;
    }

    private function fetchFromOpenAlexByOrcid(User $user, &$completePapers, &$incompletePapers)
    {
        try {
            $orcidId = trim($user->orcid);
            if (!str_starts_with($orcidId, 'https://orcid.org/')) {
                $orcidId = 'https://orcid.org/' . $orcidId;
            }

            $authorRes = Http::withHeaders(['Accept' => 'application/json'])->get("https://api.openalex.org/authors/{$orcidId}");
            if (!$authorRes->successful()) return;

            $worksApiUrl = $authorRes->json('works_api_url');
            if (!$worksApiUrl) return;

            $worksApiUrl .= (str_contains($worksApiUrl, '?') ? '&' : '?') . 'per-page=200';
            $worksRes = Http::withHeaders(['Accept' => 'application/json'])->get($worksApiUrl);

            if (!$worksRes->successful()) return;

            $works = $worksRes->json('results');
            if (!$works || !is_array($works)) return;

            foreach ($works as $work) {
                $paperName = $work['title'] ?? null;
                if (!$paperName) continue;

                $existingPaper = Paper::whereRaw('LOWER(paper_name) = ?', [strtolower(trim($paperName))])->first();

                $paper_yearpub = $work['publication_year'] ?? null;
                $paper_citation = $work['cited_by_count'] ?? 0;
                $paper_doi = !empty($work['doi']) ? str_replace('https://doi.org/', '', $work['doi']) : null;

                $paper_type = 'Article';
                if (isset($work['type'])) {
                   if (str_contains(strtolower($work['type']), 'book-chapter')) $paper_type = 'Book Chapter';
                   elseif (str_contains(strtolower($work['type']), 'conference')) $paper_type = 'Conference Proceeding';
                }

                $abstract = null;
                if (isset($work['abstract_inverted_index'])) {
                    $words = [];
                    foreach ($work['abstract_inverted_index'] as $word => $positions) {
                        foreach ($positions as $pos) $words[$pos] = $word;
                    }
                    ksort($words);
                    $abstract = implode(' ', $words);
                }

                $keywords = null;
                if (!empty($work['concepts'])) {
                     $kwArray = [];
                     foreach ($work['concepts'] as $concept) {
                         if (($concept['score'] ?? 0) > 0.3) $kwArray[] = $concept['display_name'];
                     }
                     if (!empty($kwArray)) $keywords = implode(', ', array_slice($kwArray, 0, 10));
                }

                $sourceTitle = $work['primary_location']['source']['display_name'] ?? null;

                if ($existingPaper) {
                    $isUpdated = false;
                    if (empty($existingPaper->abstract) && !empty($abstract)) { $existingPaper->abstract = $abstract; $isUpdated = true; }
                    if (empty($existingPaper->keyword) && !empty($keywords)) { $existingPaper->keyword = $keywords; $isUpdated = true; }
                    if (empty($existingPaper->paper_doi) && !empty($paper_doi)) { $existingPaper->paper_doi = $paper_doi; $isUpdated = true; }
                    if (empty($existingPaper->paper_yearpub) && !empty($paper_yearpub)) { $existingPaper->paper_yearpub = $paper_yearpub; $isUpdated = true; }
                    if ($paper_citation > $existingPaper->paper_citation) { $existingPaper->paper_citation = $paper_citation; $isUpdated = true; }
                    
                    if ($isUpdated) {
                        $existingPaper->save();
                    }
                    
                    $exists = $existingPaper->teacher()->where('user_id', $user->id)->exists();
                    if (!$exists) {
                         $existingPaper->teacher()->attach($user->id, ['author_type' => 2]);
                    }
                    continue;
                }

                $paper = new Paper;
                $paper->paper_name = $paperName;
                $paper->abstract = $abstract;
                $paper->paper_type = $paper_type;
                $paper->paper_sourcetitle = $sourceTitle;
                $paper->keyword = $keywords;
                $paper->paper_url = $work['id'];
                $paper->paper_yearpub = $paper_yearpub;
                $paper->paper_volume = $work['biblio']['volume'] ?? null;
                $paper->paper_issue = $work['biblio']['issue'] ?? null;
                $paper->paper_citation = $paper_citation;
                $paper->paper_page = ($work['biblio']['first_page'] ?? '') . (($work['biblio']['first_page'] ?? false) && ($work['biblio']['last_page'] ?? false) ? '-' : '') . ($work['biblio']['last_page'] ?? '');
                $paper->paper_doi = $paper_doi;
                $paper->publication_status = 1;
                $paper->save();

                $missingFields = [];
                if (empty($paper->abstract)) $missingFields[] = 'Abstract';
                if (empty($paper->keyword)) $missingFields[] = 'Keywords';
                if (empty($paper->paper_doi)) $missingFields[] = 'DOI';

                if (empty($missingFields)) rsort($completePapers);
                else rsort($incompletePapers); // just mock adding since cronjob logs it differently

                $source = Source_data::find(1);
                if ($source) $paper->source()->sync([$source->id]);

                 foreach ($work['authorships'] ?? [] as $oaAuthor) {
                      $rawName = $oaAuthor['raw_author_name'] ?? $oaAuthor['author']['display_name'] ?? '';
                      $parts = explode(' ', trim($rawName));
                      $surname = array_pop($parts);
                      $givenName = implode(' ', $parts);

                      $posStr = $oaAuthor['author_position'] ?? 'middle';
                      $author_type = ($posStr === 'first') ? 1 : (($posStr === 'last') ? 3 : 2);

                      $isSameUser = stripos($rawName, $user->fname_en) !== false && stripos($rawName, $user->lname_en) !== false;

                      if ($isSameUser) {
                           $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                      } else {
                           $existingUser = User::whereRaw("LOWER(CONCAT(fname_en, ' ', lname_en)) = ?", [strtolower($rawName)])
                               ->orWhere(function($q) use ($givenName, $surname) {
                                  $q->where('fname_en', 'LIKE', "%{$givenName}%")->where('lname_en', 'LIKE', "%{$surname}%");
                               })->first();

                           if ($existingUser) {
                               $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                           } else {
                               $existingAuthor = Author::whereRaw("LOWER(CONCAT(author_fname, ' ', author_lname)) = ?", [strtolower($rawName)])
                                    ->orWhere(function($q) use ($givenName, $surname) {
                                        $q->where('author_fname', 'LIKE', "%{$givenName}%")->where('author_lname', 'LIKE', "%{$surname}%");
                                    })->first();

                               if (!$existingAuthor) {
                                   $newAuthor = new Author;
                                   $newAuthor->author_fname = $givenName ?: $rawName;
                                   $newAuthor->author_lname = $surname ?: '-';
                                   $newAuthor->save();
                                   $paper->author()->attach($newAuthor->id, ['author_type' => $author_type]);
                               } else {
                                   $paper->author()->attach($existingAuthor->id, ['author_type' => $author_type]);
                               }
                           }
                      }
                 }

                 $exists = $paper->teacher()->where('user_id', $user->id)->exists();
                 if (!$exists) $paper->teacher()->attach($user->id, ['author_type' => 2]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch OpenAlex via ORCID for user_id: {$user->id}. " . $e->getMessage());
        }
    }
}
