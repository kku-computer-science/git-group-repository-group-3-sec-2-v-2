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

class Scopus extends Command
{
    protected $signature = 'scopus:cron';
    protected $description = 'Update papers from Scopus API';
    private const SCOPUS_API_KEY = 'c9505cb6a621474141aeb03dcde91963';

    public function handle()
    {
        try {
            Log::info("Scopus update started");
            
            $users = User::whereNotNull('academic_ranks_en')->get();
            if ($users->isEmpty()) {
                Log::error("No users with academic ranks found");
                return;
            }

            foreach ($users as $user) {
                $this->processUser($user);
            }

            Log::info("Scopus update completed");

        } catch (\Exception $e) {
            Log::error("Scopus update error: " . $e->getMessage());
        }
    }

    private function processUser($user)
    {
        try {
            $fname = substr($user->fname_en, 0, 1);
            $lname = $user->lname_en;
            $id = $user->id;

            Log::info("Processing user: $lname, $fname");

            $url = Http::get('https://api.elsevier.com/content/search/scopus?', [
                'query' => "AUTHOR-NAME($lname,$fname)",
                'apikey' => self::SCOPUS_API_KEY,
            ])->json();

            if (!isset($url["search-results"]["entry"])) {
                Log::info("No papers found for user: $id");
                return;
            }

            $content = $url["search-results"]["entry"];
            $links = $url["search-results"]["link"];

            do {
                $ref = 'prev';
                foreach ($links as $link) {
                    if ($link['@ref'] == 'next') {
                        $link2 = $link['@href'];
                        $link2 = Http::get("$link2")->json();
                        $links = $link2["search-results"]["link"];
                        $nextcontent = $link2["search-results"]["entry"];
                        foreach ($nextcontent as $item) {
                            array_push($content, $item);
                        }
                    }
                }
            } while ($ref != 'prev');

            foreach ($content as $item) {
                if (array_key_exists('error', $item)) {
                    continue;
                }

                $this->processPaper($item, $user);
            }

        } catch (\Exception $e) {
            Log::error("Error processing user {$user->id}: " . $e->getMessage());
        }
    }

    private function processPaper($item, $user)
    {
        try {
            // ตรวจสอบว่ามี paper อยู่แล้วหรือไม่
            $existingPaper = Paper::where('paper_name', '=', $item['dc:title'])->first();

            if ($existingPaper) {
                $paperid = $existingPaper->id;
                $hasTask = $user->paper()->where('paper_id', $paperid)->exists();
                
                if ($hasTask != $paperid) {
                    $useaut = Author::where([
                        ['author_fname', '=', $user->fname_en],
                        ['author_lname', '=', $user->lname_en]
                    ])->first();

                    if ($useaut != null) {
                        $existingPaper->author()->detach($useaut);
                        $existingPaper->teacher()->attach($user->id);
                    } else {
                        $existingPaper->teacher()->attach($user->id);
                    }
                }
                return;
            }

            // สร้าง paper ใหม่
            $scoid = explode(":", $item['dc:identifier'])[1];
            $all = Http::get("https://api.elsevier.com/content/abstract/scopus_id/{$scoid}?filed=authors&apiKey=" . self::SCOPUS_API_KEY . "&httpAccept=application%2Fjson");
            
            $paper = new Paper;
            $paper->paper_name = $item['dc:title'];
            $paper->paper_type = $item['prism:aggregationType'];
            $paper->paper_subtype = $item['subtypeDescription'];
            $paper->paper_sourcetitle = $item['prism:publicationName'];
            $paper->paper_url = $item['link'][2]['@href'];
            $paper->paper_yearpub = Carbon::parse($item['prism:coverDate'])->format('Y');
            $paper->paper_volume = array_key_exists('prism:volume', $item) ? $item['prism:volume'] : null;
            $paper->paper_issue = array_key_exists('prism:issueIdentifier', $item) ? $item['prism:issueIdentifier'] : null;
            $paper->paper_citation = $item['citedby-count'];
            $paper->paper_page = $item['prism:pageRange'];
            $paper->paper_doi = array_key_exists('prism:doi', $item) ? $item['prism:doi'] : null;

            if (array_key_exists('item', $all['abstracts-retrieval-response'])) {
                $abstractData = $all['abstracts-retrieval-response']['item'];
                
                if (array_key_exists('xocs:meta', $abstractData)) {
                    if (array_key_exists('xocs:funding-text', $abstractData['xocs:meta']['xocs:funding-list'])) {
                        $paper->paper_funder = json_encode($abstractData['xocs:meta']['xocs:funding-list']['xocs:funding-text']);
                    }
                }

                $paper->abstract = $abstractData['bibrecord']['head']['abstracts'];

                if (array_key_exists('author-keywords', $abstractData['bibrecord']['head']['citation-info'])) {
                    $paper->keyword = json_encode($abstractData['bibrecord']['head']['citation-info']['author-keywords']['author-keyword']);
                }
            }

            $paper->save();

            // เชื่อมกับ source data
            $source = Source_data::findOrFail(1);
            $paper->source()->sync($source);

            // จัดการข้อมูลผู้แต่ง
            if (isset($all['abstracts-retrieval-response']['authors']['author'])) {
                $all_au = $all['abstracts-retrieval-response']['authors']['author'];
                $x = 1;
                $length = count($all_au);

                foreach ($all_au as $i) {
                    $givenName = isset($i['ce:given-name']) ? $i['ce:given-name'] : $i['preferred-name']['ce:given-name'];
                    
                    $userAuthor = User::where([
                        ['fname_en', '=', $givenName],
                        ['lname_en', '=', $i['ce:surname']]
                    ])->orWhere([
                        [DB::raw("concat(left(fname_en,1),'.')"), '=', $givenName],
                        ['lname_en', '=', $i['ce:surname']]
                    ])->first();

                    if (!$userAuthor) {
                        $author = Author::where([
                            ['author_fname', '=', $givenName],
                            ['author_lname', '=', $i['ce:surname']]
                        ])->first();

                        if (!$author) {
                            $author = new Author;
                            $author->author_fname = $givenName;
                            $author->author_lname = $i['ce:surname'];
                            $author->save();
                        }

                        if ($x === 1) {
                            $paper->author()->attach($author->id, ['author_type' => 1]);
                        } else if ($x === $length) {
                            $paper->author()->attach($author->id, ['author_type' => 3]);
                        } else {
                            $paper->author()->attach($author->id, ['author_type' => 2]);
                        }
                    } else {
                        if ($x === 1) {
                            $paper->teacher()->attach($userAuthor->id, ['author_type' => 1]);
                        } else if ($x === $length) {
                            $paper->teacher()->attach($userAuthor->id, ['author_type' => 3]);
                        } else {
                            $paper->teacher()->attach($userAuthor->id, ['author_type' => 2]);
                        }
                    }
                    $x++;
                }
            }

            Log::info("Created new paper: {$paper->id}");

        } catch (\Exception $e) {
            Log::error("Error processing paper: " . $e->getMessage());
        }
    }
}