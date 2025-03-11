<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BibtexController extends Controller
{
    function index($id)
    {
        $paper = Paper::with([
            'teacher' => function ($query) {
                $query->select(DB::raw("CONCAT(fname_en,' ',lname_en) as full_name"))
                      ->addSelect('user_papers.author_type');
            },
            'author' => function ($query) {
                $query->select(DB::raw("CONCAT(author_fname,' ',author_lname) as full_name"))
                      ->addSelect('author_of_papers.author_type');
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
        
        // Create a safe bibtex key
        $k = explode(" ", $paper[0]['author'][0]['full_name'])[0];
        $key = $k . $paper[0]['paper_yearpub'] . substr($paper[0]['paper_name'], 0, 5);
        $key = preg_replace('/[^a-z0-9]/', '', strtolower($key));

        $title = $paper[0]['paper_name'];
        $type = $paper[0]['paper_type'];
        $author = $author[0];
        $journal = $paper[0]['paper_sourcetitle'];
        $volume = $paper[0]['paper_volume'];
        $number = $paper[0]['paper_citation'];
        $page = $paper[0]['paper_page'];
        $year = $paper[0]['paper_yearpub'];
        $doi = $paper[0]['paper_doi'];

        // Create BibTeX entry
        $bibtex = "@article{" . $key . ",\n";
        $bibtex .= "  author = {" . $this->cleanBibtex($author) . "},\n";
        $bibtex .= "  title = {" . $this->cleanBibtex($title) . "},\n";
        $bibtex .= "  journal = {" . $this->cleanBibtex($journal) . "},\n";
        $bibtex .= "  year = {" . $year . "},\n";
        if ($volume) $bibtex .= "  volume = {" . $volume . "},\n";
        if ($number) $bibtex .= "  number = {" . $number . "},\n";
        if ($page) $bibtex .= "  pages = {" . $page . "},\n";
        if ($doi) $bibtex .= "  doi = {" . $doi . "},\n";
        $bibtex .= "}\n";

        return response($bibtex, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function getbib($id)
    {
        $paper = Paper::with([
            'teacher' => function ($query) {
                $query->select(DB::raw("CONCAT(fname_en,' ',lname_en) as full_name"))
                      ->addSelect('user_papers.author_type');
            },
            'author' => function ($query) {
                $query->select(DB::raw("CONCAT(author_fname,' ',author_lname) as full_name"))
                      ->addSelect('author_of_papers.author_type');
            }
        ])->find($id);

        if (!$paper) {
            return response()->json(['error' => 'Paper not found'], 404);
        }

        // Collect and sort authors
        $authors = collect([])
            ->concat($paper->teacher)
            ->concat($paper->author)
            ->sortBy('author_type')
            ->pluck('full_name')
            ->toArray();

        // Create APA citation
        $citation = $this->formatAPACitation(
            $authors,
            $paper->paper_name,
            $paper->paper_sourcetitle,
            $paper->paper_volume,
            $paper->paper_issue,
            $paper->paper_page,
            $paper->paper_yearpub,
            $paper->paper_doi
        );

        return response()->json($citation, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function formatAPACitation($authors, $title, $journal, $volume, $issue, $pages, $year, $doi)
    {
        $html = '<div class="bibtex-biblio">';
        
        // Authors
        $html .= htmlspecialchars($this->formatAuthors($authors) ?? '', ENT_QUOTES, 'UTF-8');
        
        // Year
        $html .= " ({$year}). ";
        
        // Title
        $html .= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') . ". ";
        
        // Journal
        $html .= '<i>' . htmlspecialchars($journal ?? '', ENT_QUOTES, 'UTF-8') . '</i>';
        
        // Volume, Issue, Pages
        if ($volume) {
            $html .= ", " . $volume;
            if ($issue) {
                $html .= "(" . $issue . ")";
            }
        }
        
        if ($pages) {
            $html .= ", " . $pages;
        }
        
        $html .= ".";

        // DOI
        if ($doi) {
            $html .= ' <a href="https://doi.org/' . htmlspecialchars($doi ?? '', ENT_QUOTES, 'UTF-8') . 
                     '" target="_blank">https://doi.org/' . htmlspecialchars($doi ?? '', ENT_QUOTES, 'UTF-8') . '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    private function formatAuthors($authors)
    {
        if (empty($authors)) {
            return '';
        }

        // Clean author names
        $authors = array_map(function($author) {
            return html_entity_decode(trim($author), ENT_QUOTES, 'UTF-8');
        }, $authors);

        if (count($authors) === 1) {
            return $authors[0];
        }

        if (count($authors) === 2) {
            return $authors[0] . ' & ' . $authors[1];
        }

        $lastAuthor = array_pop($authors);
        return implode(', ', $authors) . ', & ' . $lastAuthor;
    }

    private function cleanBibtex($text)
    {
        // Remove special LaTeX characters or escape them
        $text = str_replace(
            ['$', '%', '&', '#', '_', '{', '}', '\\'],
            ['\$', '\%', '\&', '\#', '\_', '\{', '\}', '\\\\'],
            $text
        );
        
        // Convert special characters to LaTeX commands
        $text = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['{\\\'a}', '{\\\'e}', '{\\\'i}', '{\\\'o}', '{\\\'u}', '{\~n}', '{\\"u}'],
            $text
        );

        return $text;
    }
}