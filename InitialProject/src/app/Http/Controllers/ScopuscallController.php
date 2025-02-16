<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use App\Models\Paper;
use App\Models\Source_data;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

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
        // Decrypt the user ID and retrieve the user from the database.
        $userId = Crypt::decrypt($id);
        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Create an array to store the names of newly inserted papers.
        $insertedPapers = [];

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
            return redirect()->back()->with('error', 'Failed to fetch Scopus search data.');
        }

        $entries = $searchResponse->json('search-results.entry');
        if (!$entries || !is_array($entries)) {
            return redirect()->back()->with('error', 'No papers found on Scopus.');
        }

        // Process each entry from Scopus.
        foreach ($entries as $item) {
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

            // Create a new Paper instance and save it.
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
            $paper->keyword           = !empty($item['author-keywords'])
                                        ? json_encode($item['author-keywords'], JSON_UNESCAPED_UNICODE)
                                        : null;
            $paper->paper_url         = $paper_url;
            $paper->publication       = $item['prism:publicationName'] ?? null;
            $paper->paper_yearpub     = $paper_yearpub;
            $paper->paper_volume      = $item['prism:volume'] ?? null;
            $paper->paper_issue       = $item['prism:issueIdentifier'] ?? '-';
            $paper->paper_citation    = $item['citedby-count'] ?? 0;
            $paper->paper_page        = $item['prism:pageRange'] ?? '-';
            $paper->paper_doi         = $item['prism:doi'] ?? null;
            $paper->paper_funder      = $paper_funder;
            $paper->reference_number  = null;
            $paper->save();

            // Attach Source Data (assume id=1 represents Scopus).
            $source = Source_data::find(1);
            if ($source) {
                $paper->source()->sync([$source->id]);
            }

            // Attach Authors.
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

                    // Check if the author matches the current user.
                    $isSameUser = false;
                    if (
                        strcasecmp($givenName, $user->fname_en) === 0 &&
                        strcasecmp($surname, $user->lname_en) === 0
                    ) {
                        $isSameUser = true;
                    } else {
                        // If first letter matches and surname matches, and affiliation contains "Khon Kaen".
                        $firstApi = strtolower(substr($givenName, 0, 1));
                        $firstUser = strtolower(substr($user->fname_en, 0, 1));
                        if ($firstApi === $firstUser && strcasecmp($surname, $user->lname_en) === 0) {
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

            // Attach the user who performed the import.
            $paper->teacher()->attach($user->id);

            // Save the name of the inserted paper.
            $insertedPapers[] = $paper->paper_name;
        }

        // If no new paper was inserted, return with an info flash message.
        if (empty($insertedPapers)) {
            return redirect()->back()->with('info', 'No changes were made.');
        }

        return redirect()->back()->with('insertedPapers', $insertedPapers);
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
}
