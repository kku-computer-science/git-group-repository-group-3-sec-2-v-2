<?php

namespace App\Http\Controllers;

use App\Models\Academicwork;
use App\Models\Author;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function request($id){
        try {
            $payload = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        $profileType = 'user';
        $profileId = $payload;

        if (is_array($payload)) {
            $profileType = $payload['type'] ?? 'user';
            $profileId = $payload['id'] ?? null;
        }

        if (!$profileId) {
            abort(404);
        }

        $user = null;
        $author = null;

        if ($profileType === 'author') {
            $author = Author::find($profileId);
        } else {
            $user = User::with(['education', 'expertise', 'program'])->where('id', $profileId)->first();

            if (!$user) {
                $author = Author::find($profileId);
                $profileType = 'author';
            }
        }

        if ($profileType === 'author') {
            if (!$author) {
                abort(404);
            }

            $res = (object) [
                'id' => $author->id,
                'profile_type' => 'author',
                'profile_picture_url' => $author->picture ? asset('images/imag_user/' . $author->picture) : asset('img/default-profile.png'),
                'position_th' => $author->academic_ranks_th ?? '',
                'fname_th' => $author->author_fname ?? '',
                'lname_th' => $author->author_lname ?? '',
                'doctoral_degree' => $author->doctoral_degree ? 'Ph.D.' : null,
                'fname_en' => $author->author_fname ?? '',
                'lname_en' => $author->author_lname ?? '',
                'academic_ranks_en' => $author->academic_ranks_en ?? '',
                'academic_ranks_th' => $author->academic_ranks_th ?? '',
                'email' => null,
                'orcid' => null,
                'education' => collect(),
                'expertise' => collect(),
                'program' => (object) ['program_name_en' => ''],
                'affiliation' => $author->belong_to ?? null,
            ];

            $paperRelation = 'author';
            $paperRelationTable = 'authors';
            $academicworkRelation = 'author';
            $paperDetailUserId = null;
            $showExport = false;
        } else {
            if (!$user) {
                abort(404);
            }

            $user->profile_type = 'user';
            $user->profile_picture_url = $user->picture ?? asset('img/default-profile.png');
            $user->affiliation = null;

            $res = $user;
            $paperRelation = 'teacher';
            $paperRelationTable = 'users';
            $academicworkRelation = 'user';
            $paperDetailUserId = $user->id;
            $showExport = true;
        }

        $teachers = collect();

        $papers = Paper::with('teacher','author','source')->whereHas($paperRelation, function($query) use($profileId, $paperRelationTable) {
            $query->where($paperRelationTable . '.id', '=', $profileId);
        })->orderBy('paper_yearpub', 'desc')-> get();

        $papers_scopus = $papers->filter(function ($paper) {
            return $paper->source->contains('source_data_id', 1);
        })->values();

        $papers_wos = $papers->filter(function ($paper) {
            return $paper->source->contains('source_data_id', 2);
        })->values();
        
        $papers_tci = $papers->filter(function ($paper) {
            return $paper->source->contains('source_data_id', 3);
        })->values();

        // $papers_tci = Paper::with('teacher','author')->whereHas('teacher', function($query) use($id) {
        //     $query->where('users.id', '=', $id);
        // })->whereHas('source', function($query) {
        //     $query->where('source_data_id', '=', 3);
        // })-> get();

        // $book_chapter = Paper::with('teacher','author')->whereHas('teacher', function($query) use($id) {
        //     $query->where('users.id', '=', $id);
        // })->whereHas('source', function($query) {
        //     $query->where('source_data_id', '=', 4);
        // })-> get();

        $book_chapter = Academicwork::with('user','author')->whereHas($academicworkRelation, function($query) use($profileId, $academicworkRelation) {
            $table = $academicworkRelation === 'author' ? 'authors' : 'users';
            $query->where($table . '.id', '=', $profileId);
        })->where('ac_type', '=', 'book')->get();

       

        $patent = Academicwork::with('user','author')->whereHas($academicworkRelation, function($query) use($profileId, $academicworkRelation) {
            $table = $academicworkRelation === 'author' ? 'authors' : 'users';
            $query->where($table . '.id', '=', $profileId);
        })->where('ac_type', '!=', 'book')->get();
        //return $res;

        $year = range(Carbon::now()->year-5, Carbon::now()->year);
        
        $scopus_counts = Paper::whereHas('source', function ($query) {
            $query->where('source_data_id', '=', 1);
        })->whereHas($paperRelation, function($query) use($profileId, $paperRelationTable) {
            $query->where($paperRelationTable . '.id', '=', $profileId);
        })->select(DB::raw('paper_yearpub as year'), DB::raw('count(*) as count'))
        ->groupBy('year')->pluck('count', 'year')->toArray();

        $tci_counts = Paper::whereHas('source', function ($query) {
            $query->where('source_data_id', '=', 3);
        })->whereHas($paperRelation, function($query) use($profileId, $paperRelationTable) {
            $query->where($paperRelationTable . '.id', '=', $profileId);
        })->select(DB::raw('paper_yearpub as year'), DB::raw('count(*) as count'))
        ->groupBy('year')->pluck('count', 'year')->toArray();

        $wos_counts = Paper::whereHas('source', function ($query) {
            $query->where('source_data_id', '=', 2);
        })->whereHas($paperRelation, function($query) use($profileId, $paperRelationTable) {
            $query->where($paperRelationTable . '.id', '=', $profileId);
        })->select(DB::raw('paper_yearpub as year'), DB::raw('count(*) as count'))
        ->groupBy('year')->pluck('count', 'year')->toArray();

        $paper_tci = [];
        $paper_scopus = [];
        $paper_wos = [];
        foreach ($year as $value) {
            $paper_scopus[] = $scopus_counts[$value] ?? 0;
            $paper_tci[] = $tci_counts[$value] ?? 0;
            $paper_wos[] = $wos_counts[$value] ?? 0;
        }

        $year2 = range(Carbon::now()->year-20, Carbon::now()->year);
        $paper_tci_s = [];
        $paper_scopus_s = [];
        $paper_wos_s = [];
        foreach ($year2 as $value) {
            $paper_scopus_s[] = $scopus_counts[$value] ?? 0;
            $paper_tci_s[] = $tci_counts[$value] ?? 0;
            $paper_wos_s[] = $wos_counts[$value] ?? 0;
        }

        $book_counts = Academicwork::where('ac_type', '=', 'book')
            ->whereHas($academicworkRelation, function($query) use($profileId, $academicworkRelation) {
                $table = $academicworkRelation === 'author' ? 'authors' : 'users';
                $query->where($table . '.id', '=', $profileId);
            })->select(DB::raw('YEAR(ac_year) as year'), DB::raw('count(*) as count'))
            ->groupBy('year')->pluck('count', 'year')->toArray();

        $patent_counts = Academicwork::where('ac_type', '!=', 'book')
            ->whereHas($academicworkRelation, function($query) use($profileId, $academicworkRelation) {
                $table = $academicworkRelation === 'author' ? 'authors' : 'users';
                $query->where($table . '.id', '=', $profileId);
            })->select(DB::raw('YEAR(ac_year) as year'), DB::raw('count(*) as count'))
            ->groupBy('year')->pluck('count', 'year')->toArray();

        $paper_book_s = [];
        $paper_patent_s = [];
        foreach ($year2 as $value) {
            $paper_book_s[] = $book_counts[$value] ?? 0;
            $paper_patent_s[] = $patent_counts[$value] ?? 0;
        }
        //return $paper_patent_s;
        


    	return view('researchprofiles')->with('year',json_encode($year,JSON_NUMERIC_CHECK))
                ->with('paper_tci',json_encode($paper_tci,JSON_NUMERIC_CHECK))
                ->with('paper_scopus',json_encode($paper_scopus,JSON_NUMERIC_CHECK))
                ->with('paper_wos',json_encode($paper_wos,JSON_NUMERIC_CHECK))
                ->with('paper_tci_s',json_encode($paper_tci_s,JSON_NUMERIC_CHECK))
                ->with('paper_scopus_s',json_encode($paper_scopus_s,JSON_NUMERIC_CHECK))
                ->with('paper_wos_s',json_encode($paper_wos_s,JSON_NUMERIC_CHECK))
                ->with('paper_book_s',json_encode($paper_book_s,JSON_NUMERIC_CHECK))
                ->with('paper_patent_s',json_encode($paper_patent_s,JSON_NUMERIC_CHECK))
                ->with(compact('res','teachers','papers','papers_tci','papers_scopus','papers_wos','book_chapter','patent', 'paperDetailUserId', 'showExport'));


    //return view('researchprofiles',compact('res','papers','year','paper'))->with('year',json_encode($year,JSON_NUMERIC_CHECK))->with('paper',json_encode($paper,JSON_NUMERIC_CHECK));

    }
}
