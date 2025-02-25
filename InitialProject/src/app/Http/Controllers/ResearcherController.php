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
    
        // ดึงข้อมูลโปรแกรมพร้อมนักวิจัย (is_research = 1)
        $programs = Program::with(['users' => function ($query) use ($search) {
            $query->where('is_research', 1) // ดึงทุก role ที่เป็นนักวิจัย
                  ->with(['expertise', 'roles']) // โหลด roles ด้วย
                  ->when($search, function ($q) use ($search) {
                      $q->where(function ($innerQ) use ($search) {
                          $innerQ->where('fname_en', 'LIKE', "%{$search}%")
                                 ->orWhere('lname_en', 'LIKE', "%{$search}%")
                                 ->orWhere('email', 'LIKE', "%{$search}%")
                                 ->orWhereHas('expertise', function ($expertiseQuery) use ($search) {
                                     $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%");
                                 });
                      });
                  })
                  // เรียงตามตำแหน่ง
                  ->orderByRaw("
                        FIELD(position_en,
                            'Prof. Dr.',
                            'Assoc. Prof. Dr.',
                            'Asst. Prof. Dr.',
                            'Assoc. Prof.',
                            'Asst. Prof.',
                            'Lecturer')
                  ")
                  // เรียงให้ Ph.D. มาก่อน
                  ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
                  ->orderBy('fname_en');
        }])->get();
    
        // เก็บ ID ของ Program ที่มีนักวิจัย (หลังกรองแล้ว) เพื่อให้ Accordion ขยายได้
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
    
        // ค้นหานักวิจัยในโปรแกรม (is_research = 1)
        $users = User::where('is_research', 1) // กรองเฉพาะผู้ที่เป็นนักวิจัย (ทุก role)
            ->with(['program', 'expertise', 'roles']) // โหลด roles ด้วย
            ->whereHas('program', fn($q) => $q->where('id', $id))
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('fname_en', 'LIKE', "%{$search}%")
                      ->orWhere('lname_en', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhereHas('expertise', fn($expertiseQuery) => $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%"));
            }))
            // เรียงตามตำแหน่ง
            ->orderByRaw("
                FIELD(position_en,
                    'Prof. Dr.',
                    'Assoc. Prof. Dr.',
                    'Asst. Prof. Dr.',
                    'Assoc. Prof.',
                    'Asst. Prof.',
                    'Lecturer')
            ")
            // เรียงให้ Ph.D. มาก่อน
            ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
            ->orderBy('fname_en')
            ->get();
    
        // โหลดข้อมูลโปรแกรม
        $program = Program::findOrFail($id);
    
        return view('researchers.program', compact('program', 'users', 'search'));
    }
    
}
