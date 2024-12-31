<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lib\Bibtex; // Ensure this is the correct namespace for the Bibtex class

class BibtexController extends Controller
{
    function index($id)
    {
        $paper = Paper::with([
            'teacher' => function ($query) {
                $query->select(DB::raw("CONCAT(fname_en,' ',lname_en) as full_name"))->addSelect('user_papers.author_type');
            },
            'author' => function ($query) {
                $query->select(DB::raw("CONCAT(author_fname,' ',author_lname) as full_name"))->addSelect('author_of_papers.author_type');
            },

        ])->find([$id])->toArray();

        $author = array_map(function ($tag) {
            $t = collect($tag['teacher']);
            $a = collect($tag['author']);
            $aut = $t->concat($a);
            $aut = $aut->sortBy(['author_type', 'asc']);
            $sorted = $aut->implode('full_name', ' and ');
            return $sorted;
        }, $paper);

        $id = $paper[0]['id'];

        $k = explode(" ", $paper[0]['author'][0]['full_name'])[0];
        $key = $k . $paper[0]['paper_yearpub'] . substr($paper[0]['paper_name'], 0, 5);
        $key = strtolower($key);
        $title = $paper[0]['paper_name'];
        $type = $paper[0]['paper_type'];

        $author = $author[0];
        $journal = $paper[0]['paper_sourcetitle'];
        $volume = $paper[0]['paper_volume'];
        $number = $paper[0]['paper_citation'];
        $page = $paper[0]['paper_page'];
        $year = $paper[0]['paper_yearpub'];
        $doi = $paper[0]['paper_doi'];

        $arr = array("type" => "Article", "key" => "watchara", "author" => $author, "title" => $title, "journal" => $journal, "volume" => $volume, "number" => $number, 'year' => $year, 'pages' => $page, 'doi' => $doi);

        $Path['lib'] = app_path('Lib/'); // Correct the path to the lib directory
        require_once $Path['lib'] . 'lib_bibtex.inc.php';

        $Site = array();
        $name = "test.bib";
        $Site['bibtex'] = new Bibtex($name);
        $bb = $Site['bibtex'];

        if (array_key_exists($key, $bb->bibarr)) {
            return view('test', compact('key', 'bb'));
        } else {
            $fp = fopen($name, 'a');
            $text = "
        @article{" . $key . ",
            author    = {" . $author . "},
            title     = {" . $title . "},
            journal   = {" . $journal . "},
            year      = {" . $year . "},
            pages      = {" . $page . "},
            doi      = {" . $doi . "},
            number      = {" . $number . "},
            volume      = {" . $volume . "},
          }";
            fwrite($fp, $text);
            fclose($fp);
            $Site['bibtex'] = new Bibtex($name);
            $bb = $Site['bibtex'];

            return view('test', compact('key', 'bb'));
        }

        return response()->json($bb);
    }

    public function getbib($id)
    {
        $name = $this->index($id);
        return $name;
    }
}
