<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ExportController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:export')->only('index');
        // Redirect::to('dashboard')->send();
         
    }
    public function index()
    {
        $data = User::with('program')->role('teacher')->orderBy('fname_en', 'desc')->paginate(15)->withQueryString();
        return view('export.index', compact('data'));
    }
}
