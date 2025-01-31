<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Bibtex;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor;

class HomeController extends Controller
{
    public function index()
    {
        $years = range(Carbon::now()->year, Carbon::now()->year - 5);
        $from = Carbon::now()->year - 16;
        $to = Carbon::now()->year - 6;
        
        // Only get years for accordion headers, not the actual data
        $papers = array_fill_keys($years, null);
        $papers[$to] = null;

        $year = range(Carbon::now()->year - 4, Carbon::now()->year);
        $num = $this->getnum();
        
        return view('home', compact('papers'))
            ->with('year', json_encode($year, JSON_NUMERIC_CHECK))
            ->with('paper_tci', json_encode($this->getYearlyPapers($year, 3), JSON_NUMERIC_CHECK))
            ->with('paper_scopus', json_encode($this->getYearlyPapers($year, 1), JSON_NUMERIC_CHECK))
            ->with('paper_wos', json_encode($this->getYearlyPapers($year, 2), JSON_NUMERIC_CHECK))
            ->with('paper_tci_numall', json_encode($num['paper_tci'], JSON_NUMERIC_CHECK))
            ->with('paper_scopus_numall', json_encode($num['paper_scopus'], JSON_NUMERIC_CHECK))
            ->with('paper_wos_numall', json_encode($num['paper_wos'], JSON_NUMERIC_CHECK));
    }

    private function getYearlyPapers($years, $sourceDataId)
    {
        $papers = [];
        foreach ($years as $year) {
            $count = Paper::whereHas('source', function ($query) use ($sourceDataId) {
                return $query->where('source_data_id', '=', $sourceDataId);
            })
            ->whereIn('paper_type', ['Conference Proceeding', 'Journal'])
            ->where('paper_yearpub', $year)
            ->count();
            
            $papers[] = $count;
        }
        return $papers;
    }

    public function getnum()
    {
        $paper_scopus = Paper::whereHas('source', function ($query) {
            return $query->where('source_data_id', '=', 1);
        })->whereIn('paper_type', ['Conference Proceeding', 'Journal'])->count();

        $paper_tci = Paper::whereHas('source', function ($query) {
            return $query->where('source_data_id', '=', 3);
        })->whereIn('paper_type', ['Conference Proceeding', 'Journal'])->count();

        $paper_wos = Paper::whereHas('source', function ($query) {
            return $query->where('source_data_id', '=', 2);
        })->whereIn('paper_type', ['Conference Proceeding', 'Journal'])->count();

        return compact('paper_scopus', 'paper_tci', 'paper_wos');
    }

    public function getPapersByYear($year)
    {
        if ($year == Carbon::now()->year - 6) {
            // Handle the "Before" section
            $from = Carbon::now()->year - 16;
            $to = Carbon::now()->year - 6;
            $papers = $this->getPapersData(null, [$from, $to]);
        } else {
            // Handle regular years
            $papers = $this->getPapersData($year);
        }
        
        return response()->json($papers);
    }

    private function getPapersData($year = null, $range = null)
    {
        $query = Paper::with([
            'teacher' => function ($query) {
                $query->select(DB::raw("CONCAT(concat(left(fname_en,1),'.'),' ',lname_en) as full_name"))
                      ->addSelect('user_papers.author_type');
            },
            'author' => function ($query) {
                $query->select(DB::raw("CONCAT(concat(left(author_fname,1),'.'),' ',author_lname) as full_name"))
                      ->addSelect('author_of_papers.author_type');
            }
        ]);

        if ($range) {
            $query->whereBetween('paper_yearpub', $range);
        } else {
            $query->where('paper_yearpub', '=', $year);
        }

        $papers = $query->orderBy('paper_yearpub', 'desc')->get()->toArray();

        return array_map(function ($tag) {
            $t = collect($tag['teacher']);
            $a = collect($tag['author']);
            $aut = $t->concat($a);
            $aut = $aut->sortBy(['author_type', 'asc']);
            $sorted = $aut->implode('full_name', ', ');
            
            return [
                'id' => $tag['id'],
                'author' => $sorted,
                'paper_name' => $tag['paper_name'],
                'paper_sourcetitle' => $tag['paper_sourcetitle'],
                'paper_type' => $tag['paper_type'],
                'paper_volume' => $tag['paper_volume'],
                'paper_yearpub' => $tag['paper_yearpub'],
                'paper_url' => $tag['paper_url'],
                'paper_doi' => $tag['paper_doi']
            ];
        }, $papers);
    }

    public function bibtex($id)
    {
        $paper = Paper::with(['author' => function ($query) {
            $query->select('author_name');
        }])->find([$id])->first()->toArray();

        $Path['lib'] = './../lib/';
        require_once $Path['lib'] . 'lib_bibtex.inc.php';

        $Site = array();
        $Site['bibtex'] = new Bibtex('references.bib');
        $bb = $Site['bibtex'];

        $title = $paper['paper_name'];
        $a = collect($paper['author']);
        $author = $a->implode('author_name', ', ');
        $journal = $paper['paper_sourcetitle'];
        $volume = $paper['paper_volume'];
        $number = $paper['paper_citation'];
        $page = $paper['paper_page'];
        $year = $paper['paper_yearpub'];
        $doi = $paper['paper_doi'];

        $key = "kku";
        $arr = array(
            "type" => $type, 
            "key" => "kku", 
            "author" => $author, 
            "title" => $title, 
            "journal" => $journal, 
            "volume" => $volume, 
            "number" => $number, 
            'year' => $year, 
            'pages' => $page, 
            'ee' => $doi
        );

        $bb->bibarr["kku"] = $arr;
        $key = "kku";

        return response()->json($key, $bb);
    }
}