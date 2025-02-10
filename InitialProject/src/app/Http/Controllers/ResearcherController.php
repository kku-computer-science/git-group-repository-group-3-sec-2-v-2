<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class ResearcherController extends Controller
{
    // แสดงรายชื่อนักวิจัยทั้งหมดในโปรแกรม
    public function index(Request $request)
    {
        // รับคำค้นหา
        $search = $request->input('textsearch');

        // ดึงข้อมูลโปรแกรมพร้อมนักวิจัย
        $programs = Program::with(['users' => function ($query) use ($search) {
            $query->role('teacher')
                ->with('expertise')
                ->when($search, function ($q) use ($search) {
                    $q->where('fname_en', 'LIKE', "%{$search}%")
                        ->orWhere('lname_en', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhereHas('expertise', function ($expertiseQuery) use ($search) {
                            $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%");
                        });
                })
                ->orderByRaw("FIELD(position_en, 'Prof. Dr.', 'Assoc. Prof. Dr.', 'Asst. Prof. Dr.', 'Assoc. Prof.', 'Asst. Prof.', 'Lecturer')")
                ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
                ->orderBy('fname_en');
        }])->get();

        // เก็บรายการโปรแกรมที่มีผลการค้นหา
        $expandedProgramIds = $programs->filter(function ($program) {
            return $program->users->isNotEmpty();
        })->pluck('id')->toArray();

        return view('researchers.index', compact('programs', 'search', 'expandedProgramIds'));
    }

    // แสดงนักวิจัยในโปรแกรมที่ระบุ
    public function program($id, Request $request)
    {
        // รับคำค้นหา
        $search = $request->input('textsearch');

        // ค้นหานักวิจัยในโปรแกรม
        $users = User::role('teacher')
            ->with(['program', 'expertise'])
            ->whereHas('program', fn($q) => $q->where('id', $id))
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('fname_en', 'LIKE', "%{$search}%")
                    ->orWhere('lname_en', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhereHas('expertise', fn($expertiseQuery) => $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%"));
            }))
            ->orderByRaw("FIELD(position_en, 'Prof. Dr.', 'Assoc. Prof. Dr.', 'Asst. Prof. Dr.', 'Assoc. Prof.', 'Asst. Prof.', 'Lecturer')")
            ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
            ->orderBy('fname_en')
            ->get();

        $program = Program::findOrFail($id);

        return view('researchers.program', compact('program', 'users', 'search'));
    }
}
