<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $yearArr = range(Carbon::now()->year - 4, Carbon::now()->year);
        $y = $yearArr;

        $getStats = function($sourceId) use ($yearArr) {
            return Paper::whereHas('source', function ($q) use ($sourceId) { 
                    $q->where('source_data_id', $sourceId); 
                })
                ->whereIn('paper_type', ['Conference Proceeding', 'Journal'])
                ->whereIn('paper_yearpub', $yearArr)
                ->select(DB::raw('paper_yearpub as year'), DB::raw('count(*) as count'), DB::raw('sum(paper_citation) as citations'))
                ->groupBy('year')
                ->get()
                ->keyBy('year');
        };

        $scopus_stats = $getStats(1);
        $wos_stats = $getStats(2);
        $tci_stats = $getStats(3);

        $paper_scopus = [];
        $paper_tci = [];
        $paper_wos = [];
        $paper_scopus_cit = [];
        $paper_tci_cit = [];
        $paper_wos_cit = [];

        foreach ($yearArr as $yearVal) {
            $paper_scopus[] = $scopus_stats->has($yearVal) ? $scopus_stats[$yearVal]->count : 0;
            $paper_scopus_cit[] = $scopus_stats->has($yearVal) ? (int)$scopus_stats[$yearVal]->citations : 0;

            $paper_wos[] = $wos_stats->has($yearVal) ? $wos_stats[$yearVal]->count : 0;
            $paper_wos_cit[] = $wos_stats->has($yearVal) ? (int)$wos_stats[$yearVal]->citations : 0;

            $paper_tci[] = $tci_stats->has($yearVal) ? $tci_stats[$yearVal]->count : 0;
            $paper_tci_cit[] = $tci_stats->has($yearVal) ? (int)$tci_stats[$yearVal]->citations : 0;
        }

        return view('report', compact('y'))->with('year', json_encode($yearArr, JSON_NUMERIC_CHECK))
            ->with('paper_tci', json_encode($paper_tci, JSON_NUMERIC_CHECK))
            ->with('paper_scopus', json_encode($paper_scopus, JSON_NUMERIC_CHECK))
            ->with('paper_wos', json_encode($paper_wos, JSON_NUMERIC_CHECK))
            ->with('paper_scopus_cit', json_encode($paper_scopus_cit, JSON_NUMERIC_CHECK))
            ->with('paper_wos_cit', json_encode($paper_wos_cit, JSON_NUMERIC_CHECK))
            ->with('paper_tci_cit', json_encode($paper_tci_cit, JSON_NUMERIC_CHECK));
    }
}
