<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScholarService;

class ScholarController extends Controller
{
    protected $scholarService;

    public function __construct(ScholarService $scholarService)
    {
        $this->scholarService = $scholarService;
    }

    public function searchScholar(Request $request)
    {
        $name = $request->query('name');
        if (!$name) {
            return response()->json(['error' => 'Name is required'], 400);
        }

        $result = $this->scholarService->searchScholar($name);
        return response()->json($result);
    }

    public function getScholar($id)
    {
        $result = $this->scholarService->getScholarInfo($id);
        return response()->json($result);
    }
}
