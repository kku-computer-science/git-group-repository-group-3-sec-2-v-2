<?php

namespace App\Http\Controllers;

use App\Exports\ExportPaper;
use App\Exports\ExportUser;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportPaperController extends Controller
{
    public function exportUsers(Request $request){
        $export = new ExportUser(); // Corrected instantiation
        return Excel::download($export, 'new.csv');
    }
}
