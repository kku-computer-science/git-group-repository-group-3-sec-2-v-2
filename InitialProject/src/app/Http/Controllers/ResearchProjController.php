<?php

namespace App\Http\Controllers;
use App\Models\ResearchProject;
use Illuminate\Http\Request;

class ResearchProjController extends Controller
{
    public function index()
    {
        $resp = ResearchProject::with('user', 'fund')->orderBy('project_year', 'desc')->paginate(10)->withQueryString();
        return view('research_proj',compact('resp'));
    }
}
