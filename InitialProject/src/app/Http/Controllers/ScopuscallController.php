<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\ActivityLog;
use App\Services\ErrorLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class ScopuscallController extends Controller
{
    /**
     * Fetch data from the Scopus API and store new Paper records.
     * This method inserts new papers only. (If a paper already exists,
     * no update or additional relation is performed.)
     *
     * @param  string  $id  (Encrypted) User ID.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create($id)
    {
        try {
            // Decrypt the user ID and retrieve the user from the database.
            $userId = Crypt::decrypt($id);
            $user = User::find($userId);
            if (!$user) {
                return redirect()->back()->with('error', 'User not found.');
            }

            // Log the start of the Scopus call process
            ActivityLog::log(
                $userId,
                'Scopus Import - Start',
                'Initiated paper import from Scopus'
            );

            // Create arrays to store the names of newly inserted papers.
            $completePapers = [];
            $incompletePapers = [];

            // ---------------------------------------------------------------------
            //                           Fetch Logic Selection
            // ---------------------------------------------------------------------
            if (!empty($user->orcid)) {
                $this->fetchFromOpenAlexByOrcid($user, $completePapers, $incompletePapers);
            } else {
                // ---------------------------------------------------------------------
                //                           Scopus API Section
                // ---------------------------------------------------------------------
            // Build the search query using the first letter of the user's first name and their last name.
            $firstLetter = substr($user->fname_en, 0, 1);
            $lname = $user->lname_en;
            $searchQuery = "AUTHOR-NAME({$lname},{$firstLetter})";

            // Call the Scopus Search API.
            $searchResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get("https://api.elsevier.com/content/search/scopus", [
                'query' => $searchQuery,
            ]);

            if (!$searchResponse->successful()) {
                // Log the API error
                $errorMessage = 'Failed to fetch Scopus search data. Status: ' . $searchResponse->status();
                ErrorLogService::logException(new Exception($errorMessage), 'ScopuscallController@create - Search API');
                ActivityLog::log(
                    $userId,
                    'Scopus Import - Error',
                    $errorMessage
                );
                return redirect()->back()->with('error', $errorMessage);
            }

            $entries = $searchResponse->json('search-results.entry');
            if (!$entries || !is_array($entries)) {
                $errorMessage = 'No papers found on Scopus.';
                ActivityLog::log(
                    $userId,
                    'Scopus Import - Complete',
                    $errorMessage
                );
                return redirect()->back()->with('info', $errorMessage);
            }

            // Process each entry from Scopus.
            foreach ($entries as $item) {
                try {
                    // Check if the paper has a title.
                    $scopusPaperName = $item['dc:title'] ?? null;
                    if (!$scopusPaperName) {
                        continue; // Skip if title is not present.
                    }

                    // Check if the paper already exists in the database.
                    $existingPaper = Paper::where('paper_name', $scopusPaperName)->first();
                    if ($existingPaper) {
                        // Skip if the paper already exists (we only insert new papers).
                        continue;
                    }

                    // If the paper is not found in the DB, proceed to insert a new record.
                    $rawScopusId = $item['dc:identifier'] ?? '';
                    $scopusId = str_replace('SCOPUS_ID:', '', $rawScopusId);
                    $detailUrl = "https://api.elsevier.com/content/abstract/scopus_id/{$scopusId}";

                    // Fetch additional details from the Abstract API.
                    $detailResponse = Http::withHeaders([
                        'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                        'Accept'       => 'application/json',
                    ])->get($detailUrl);

                    // Set default values.
                    $paper_name   = $scopusPaperName;
                    $abstract     = null;
                    $paper_funder = null;
                    $detailData   = [];

                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json('abstracts-retrieval-response.item');

                        // Use citation-title as the paper name if available.
                        if (isset($detailData['bibrecord']['head']['citation-title'])) {
                            $paper_name = $detailData['bibrecord']['head']['citation-title'];
                        }

                        // Retrieve the abstract details.
                        if (isset($detailData['bibrecord']['head']['abstracts'])) {
                            $abs = $detailData['bibrecord']['head']['abstracts'];
                            $abstract = is_array($abs)
                                ? json_encode($abs, JSON_UNESCAPED_UNICODE)
                                : $abs;
                        }

                        // Retrieve funding details from xocs:funding-text.
                        if (isset($detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'])) {
                            $funderRaw = $detailData['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                            $paper_funder = is_array($funderRaw)
                                ? json_encode($funderRaw, JSON_UNESCAPED_UNICODE)
                                : $funderRaw;
                        }
                    } else {
                        // Log the detail API error but continue processing
                        $errorMessage = 'Failed to fetch paper details for ' . $scopusPaperName . '. Status: ' . $detailResponse->status();
                        ErrorLogService::logException(new Exception($errorMessage), 'ScopuscallController@create - Detail API');
                    }

                    // Determine the paper URL using available links; fallback to $detailUrl.
                    $paper_url = $detailUrl;
                    if (!empty($item['link']) && is_array($item['link'])) {
                        foreach ($item['link'] as $linkObj) {
                            if (isset($linkObj['@ref']) && $linkObj['@ref'] === 'scopus') {
                                $paper_url = $linkObj['@href'] ?? $detailUrl;
                                break;
                            }
                        }
                    }

                    // Extract the publication year from prism:coverDate (first 4 characters).
                    $coverDate = $item['prism:coverDate'] ?? null;
                    $paper_yearpub = $coverDate ? substr($coverDate, 0, 4) : null;
                    $subtype = $item['subtype'] ?? 'ar';

                    // Convert subtype 'ar' to 'Article'.
                    if ($subtype === 'ar') {
                        $subtype = 'Article';
                    }

                    // Retrieve DOI
                    $paper_doi = $item['prism:doi'] ?? null;

                    // --------------- OPENALEX INTEGRATION -----------------
                    $openAlexAuthorships = null;
                    if ($paper_doi) {
                        try {
                            $doiClean = trim($paper_doi);
                            if (str_starts_with($doiClean, '10.')) {
                                $openAlexUrl = "https://api.openalex.org/works/https://doi.org/{$doiClean}";
                                $oaResponse = Http::withHeaders([
                                    'Accept' => 'application/json',
                                    'User-Agent' => 'mailto:admin@cpkkuhost.com' 
                                ])->timeout(10)->get($openAlexUrl);

                                if ($oaResponse->successful()) {
                                    $oaData = $oaResponse->json();
                                    
                                    // 1. Process Abstract (Override Scopus if missing/present)
                                    if (empty($abstract) && isset($oaData['abstract_inverted_index'])) {
                                        $words = [];
                                        foreach ($oaData['abstract_inverted_index'] as $word => $positions) {
                                            foreach ($positions as $pos) {
                                                $words[$pos] = $word;
                                            }
                                        }
                                        ksort($words);
                                        $abstract = implode(' ', $words);
                                    }

                                    // 2. Process Citations
                                    $newCitations = $oaData['cited_by_count'] ?? 0;
                                    $currentCitations = $item['citedby-count'] ?? 0;
                                    $item['citedby-count'] = max($newCitations, $currentCitations);

                                    // 3. Process Keywords
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
                                    
                                    // 4. Capture Authorships to use later
                                    if (!empty($oaData['authorships'])) {
                                        $openAlexAuthorships = $oaData['authorships'];
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            // Silently fail OpenAlex fetch and proceed with Scopus data
                        }
                    }
                    // ------------------------------------------------------

                    // Create a new Paper instance and save it.
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
                    if (is_array($subtypeDesc)) {
                        $subtypeDesc = json_encode($subtypeDesc, JSON_UNESCAPED_UNICODE);
                    }
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

                    // Categorize as complete or incomplete
                    $missingFields = [];
                    if (empty($paper->abstract)) $missingFields[] = 'Abstract';
                    if (empty($paper->keyword)) $missingFields[] = 'Keywords';
                    if (empty($paper->paper_doi)) $missingFields[] = 'DOI';

                    if (empty($missingFields)) {
                        $completePapers[] = $paper->paper_name;
                    } else {
                        $incompletePapers[] = $paper->paper_name . " [Missing: " . implode(', ', $missingFields) . "]";
                    }

                    // Attach Source Data (assume id=1 represents Scopus).
                    $source = Source_data::find(1);
                    if ($source) {
                        $paper->source()->sync([$source->id]);
                    }

                    // Attach Authors.
                    if ($openAlexAuthorships) {
                        // USE OPENALEX AUTHORS
                        foreach ($openAlexAuthorships as $oaAuthor) {
                            $rawName = $oaAuthor['raw_author_name'] ?? $oaAuthor['author']['display_name'] ?? '';
                            $parts = explode(' ', trim($rawName));
                            $surname = array_pop($parts);
                            $givenName = implode(' ', $parts);

                            $posStr = $oaAuthor['author_position'] ?? 'middle';
                            $author_type = ($posStr === 'first') ? 1 : (($posStr === 'last') ? 3 : 2);

                            // Match processing for Khon Kaen
                            $isSameUser = false;
                            
                            // Check explicitly against current user
                            if (stripos($rawName, $user->fname_en) !== false && stripos($rawName, $user->lname_en) !== false) {
                                $isKKU = false;
                                if (!empty($oaAuthor['institutions'])) {
                                    foreach ($oaAuthor['institutions'] as $inst) {
                                        if (stripos($inst['display_name'] ?? '', 'Khon Kaen') !== false) {
                                            $isKKU = true; break;
                                        }
                                    }
                                }
                                if (!empty($oaAuthor['affiliations'])) {
                                     foreach ($oaAuthor['affiliations'] as $aff) {
                                        if (stripos($aff['raw_affiliation_string'] ?? '', 'Khon Kaen') !== false) {
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
                                        $query->where('fname_en', 'LIKE', "%{$givenName}%")
                                              ->where('lname_en', 'LIKE', "%{$surname}%");
                                    })->first();

                                if ($existingUser) {
                                    $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                                } else {
                                    $existingAuthor = Author::whereRaw("LOWER(CONCAT(author_fname, ' ', author_lname)) = ?", [strtolower($rawName)])
                                        ->orWhere(function($query) use ($givenName, $surname) {
                                            $query->where('author_fname', 'LIKE', "%{$givenName}%")
                                                  ->where('author_lname', 'LIKE', "%{$surname}%");
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
                        // FALLBACK TO SCOPUS AUTHORS
                        $authorsData = $detailData['bibrecord']['head']['author-group']['author']
                            ?? $detailResponse->json('abstracts-retrieval-response.authors.author');
                        if ($authorsData && !is_array($authorsData)) {
                            $authorsData = [$authorsData];
                        }
                        if ($authorsData && is_array($authorsData)) {
                            $totalAuthors = count($authorsData);
                            $x = 1;
                            foreach ($authorsData as $authorItem) {
                                // Retrieve author's given name and surname.
                                $givenName = $authorItem['ce:given-name']
                                    ?? ($authorItem['preferred-name']['ce:given-name'] ?? '');
                                $surname   = $authorItem['ce:surname']
                                    ?? ($authorItem['preferred-name']['ce:surname'] ?? '');
    
                                // Determine the author order: 1 = first, 2 = co-author, 3 = last.
                                $author_type = ($x === 1) ? 1 : (($x === $totalAuthors) ? 3 : 2);
    
                                // Updated condition: require all three conditions to match
                                $isSameUser = false;
                                if (
                                    strcasecmp($givenName, $user->fname_en) === 0 &&
                                    strcasecmp($surname, $user->lname_en) === 0 &&
                                    strtolower(substr($givenName, 0, 1)) === strtolower(substr($user->fname_en, 0, 1))
                                ) {
                                    if (!empty($authorItem['affiliation']) && is_array($authorItem['affiliation'])) {
                                        foreach ($authorItem['affiliation'] as $aff) {
                                            $affName = $aff['affiliation-name'] ?? '';
                                            if (stripos($affName, 'Khon Kaen') !== false) {
                                                $isSameUser = true;
                                                break;
                                            }
                                        }
                                    }
                                }
    
                                // Attach relation based on the check.
                                if ($isSameUser) {
                                    $paper->teacher()->attach($user->id, ['author_type' => $author_type]);
                                } else {
                                    // Check in Users table.
                                    $existingUser = User::where('fname_en', $givenName)
                                        ->where('lname_en', $surname)
                                        ->first();
                                    if ($existingUser) {
                                        $paper->teacher()->attach($existingUser->id, ['author_type' => $author_type]);
                                    } else {
                                        // Check in Authors table.
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
                                }
                                $x++;
                            }
                        }
                    }

                    // Ensure user who performed import is attached (if not already)
                    $exists = $paper->teacher()->where('user_id', $user->id)->exists();
                    if (!$exists) {
                        $paper->teacher()->attach($user->id, ['author_type' => 2]);
                    }
                } catch (Exception $e) {
                    // Log the error for this specific paper but continue processing others
                    $errorMessage = 'Error processing paper: ' . ($scopusPaperName ?? 'Unknown') . '. Error: ' . $e->getMessage();
                    ErrorLogService::logException($e, 'ScopuscallController@create - Paper Processing');
                    ActivityLog::log(
                        $userId,
                        'Scopus Import - Paper Error',
                        $errorMessage
                    );
                }
            }
            } // END OF ELSE BLOCK FOR SCOPUS LOGIC

            // If no new paper was inserted, return with an info flash message.
            if (empty($completePapers) && empty($incompletePapers)) {
                // Log that no papers were found/added
                ActivityLog::log(
                    $userId,
                    'Scopus Import - Complete',
                    'No new papers found'
                );
                return redirect()->back()->with('info', 'No changes were made.');
            }

            // Build display message
            $message = '';
            if (count($completePapers) > 0) {
                $message .= "✅ Complete Papers Added (" . count($completePapers) . "):\n";
                foreach($completePapers as $p) {
                    $message .= "  - " . $p . "\n";
                }
                $message .= "\n";
            }
            if (count($incompletePapers) > 0) {
                $message .= "⚠️ Incomplete Papers Added (" . count($incompletePapers) . ") [Missing Data]:\n";
                foreach($incompletePapers as $p) {
                    $message .= "  - " . $p . "\n";
                }
            }

            // Log successful paper retrieval with count
            $totalFound = count($completePapers) + count($incompletePapers);
            ActivityLog::log(
                $userId,
                'Scopus Import - Success',
                "Successfully imported $totalFound papers.",
                $user->client_ip ?? \Request::ip()
            );

            return redirect()->back()->with('importMessage', $message);
        } catch (Exception $e) {
            // Log the main error
            ErrorLogService::logException($e, 'ScopuscallController@create - Main');
            
            // Also log to activity logs
            $userId = isset($user) ? $user->id : (isset($userId) ? $userId : null);
            if ($userId) {
                ActivityLog::log(
                    $userId,
                    'Scopus Import - Fatal Error',
                    'Error during Scopus import: ' . $e->getMessage()
                );
            }
            
            return redirect()->back()->with('error', 'An error occurred during the Scopus import: ' . $e->getMessage());
        }
    }

    /**
     * Example: Display Paper statistics for the last 5 years.
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
        // ...
    }

    public function show($id)
    {
        // ...
    }

    public function edit($id)
    {
        // ...
    }

    public function update(Request $request, $id)
    {
        // ...
    }

    public function destroy($id)
    {
        // ...
    }

    public function callAll(Request $request)
    {
        // Check admin role
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        set_time_limit(0); // Prevent timeout
        $adminId = auth()->user()->id;

        ActivityLog::log(
            $adminId,
            'Bulk Scopus Import - Start',
            'Initiated bulk paper import from Scopus for all teachers'
        );

        $teachers = User::role('teacher')->get();
        $summary = [];

        foreach ($teachers as $user) {
            $completePapers = [];
            $incompletePapers = [];

            // ---------------------------------------------------------------------
            //                           Fetch Logic Selection
            // ---------------------------------------------------------------------
            if (!empty($user->orcid)) {
                $this->fetchFromOpenAlexByOrcid($user, $completePapers, $incompletePapers);
            } else {
                // ---------------------------------------------------------------------
                //                           Scopus API Section
                // ---------------------------------------------------------------------
            $firstLetter = substr($user->fname_en, 0, 1);
            $lname = $user->lname_en;
            $searchQuery = "AUTHOR-NAME({$lname},{$firstLetter})";

            $searchResponse = Http::withHeaders([
                'X-ELS-APIKey' => 'c9505cb6a621474141aeb03dcde91963',
                'Accept'       => 'application/json',
            ])->get("https://api.elsevier.com/content/search/scopus", [
                'query' => $searchQuery,
            ]);

            if (!$searchResponse->successful()) {
                continue; // Skip user on error
            }

            $entries = $searchResponse->json('search-results.entry');
            if (!$entries || !is_array($entries)) {
                continue;
            }

            foreach ($entries as $item) {
                try {
                    // Check if the paper has a title.
                    $scopusPaperName = $item['dc:title'] ?? null;
                    if (!$scopusPaperName) continue;

                    // Check if the paper already exists
                    $existingPaper = Paper::where('paper_name', $scopusPaperName)->first();
                    if ($existingPaper) continue;

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
                        } catch (Exception $e) {}
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

                    // Track missing fields
                    $missingFields = [];
                    if (empty($paper->abstract)) $missingFields[] = 'Abstract';
                    if (empty($paper->keyword)) $missingFields[] = 'Keywords';
                    if (empty($paper->paper_doi)) $missingFields[] = 'DOI';

                    if (empty($missingFields)) {
                        $completePapers[] = $paper->paper_name;
                    } else {
                        $incompletePapers[] = $paper->paper_name . " [Missing: " . implode(', ', $missingFields) . "]";
                    }

                    $source = Source_data::find(1);
                    if ($source) $paper->source()->sync([$source->id]);

                    // Attach Authors.
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
                } catch (Exception $e) {}
            }
            } // END OF ELSE BLOCK FOR SCOPUS LOGIC

            if (!empty($completePapers) || !empty($incompletePapers)) {
                $uniqueKey = $user->fname_en . ' ' . $user->lname_en;
                $summary[$uniqueKey] = [
                    'complete' => $completePapers,
                    'incomplete' => $incompletePapers
                ];
            }
        }

        ActivityLog::log($adminId, 'Bulk Scopus Import - Success', 'Finished bulk import.', \Request::ip());
        return view('papers.import_summary', compact('summary'));
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
                if ($existingPaper) continue;

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
            ErrorLogService::logException($e, 'ScopuscallController@fetchFromOpenAlexByOrcid');
        }
    }
}
