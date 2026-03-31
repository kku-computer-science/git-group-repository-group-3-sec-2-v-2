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

    	return view('researchprofiles')->with('year',json_encode($year,JSON_NUMERIC_CHECK))
                ->with('paper_tci',json_encode($paper_tci,JSON_NUMERIC_CHECK))
                ->with('paper_scopus',json_encode($paper_scopus,JSON_NUMERIC_CHECK))
                ->with('paper_wos',json_encode($paper_wos,JSON_NUMERIC_CHECK))
                ->with('paper_tci_s',json_encode($paper_tci_s,JSON_NUMERIC_CHECK))
                ->with('paper_scopus_s',json_encode($paper_scopus_s,JSON_NUMERIC_CHECK))
                ->with('paper_wos_s',json_encode($paper_wos_s,JSON_NUMERIC_CHECK))
                ->with('paper_book_s',json_encode($paper_book_s,JSON_NUMERIC_CHECK))
                ->with('paper_patent_s',json_encode($paper_patent_s,JSON_NUMERIC_CHECK))
                ->with(compact('res','teachers', 'paperDetailUserId', 'profileId', 'profileType', 'showExport'));
    }

    public function getPapers(Request $request, $id)
    {
        $profileType = $request->query('type', 'user');
        $source = $request->query('source', 'all');
        
        $paperRelation = $profileType === 'author' ? 'author' : 'teacher';
        $paperRelationTable = $profileType === 'author' ? 'authors' : 'users';

        $query = Paper::with(['teacher', 'author', 'source'])
            ->whereHas($paperRelation, function($q) use($id, $paperRelationTable) {
                $q->where($paperRelationTable . '.id', '=', $id);
            });

        if ($source !== 'all') {
            $sourceDataId = match($source) {
                'scopus' => 1,
                'wos' => 2,
                'tci' => 3,
                default => null
            };

            if ($sourceDataId) {
                $query->whereHas('source', function ($q) use ($sourceDataId) {
                    $q->where('source_data_id', '=', $sourceDataId);
                });
            }
        }

        $papers = $query->orderBy('paper_yearpub', 'desc')->get();
        return response()->json($papers);
    }

    public function getAcademicWorks(Request $request, $id)
    {
        $profileType = $request->query('type', 'user');
        $workType = $request->query('work_type', 'book');
        
        $academicworkRelation = $profileType === 'author' ? 'author' : 'user';
        $table = $profileType === 'author' ? 'authors' : 'users';

        $query = Academicwork::with(['user', 'author'])
            ->whereHas($academicworkRelation, function($q) use($id, $table) {
                $q->where($table . '.id', '=', $id);
            });

        if ($workType === 'book') {
            $query->where('ac_type', '=', 'book');
        } else {
            $query->where('ac_type', '!=', 'book');
        }

        $works = $query->orderBy('ac_year', 'desc')->get();
        return response()->json($works);
    }
}
