<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleScholarProfileService;

class GoogleScholarProfileController extends Controller
{
    protected $scholarService;

    public function __construct(GoogleScholarProfileService $scholarService)
    {
        $this->scholarService = $scholarService;
    }

    public function getProfile($userId)
    {
        $profile = $this->scholarService->getProfile($userId);
        return response()->json($profile);
    }
}
