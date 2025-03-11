<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ResearcherController extends Controller
{
    // แสดงรายชื่อนักวิจัยทั้งหมดแยกตาม role
    public function index(Request $request)
    {
        // รับคำค้นหา
        $search = $request->input('textsearch');
        
        // ตรวจสอบภาษาปัจจุบัน
        $locale = app()->getLocale(); // 'th' หรือ 'en'
    
        // ดึงข้อมูล roles ที่ต้องการแสดง (teacher และ student) โดยเรียง teacher ขึ้นก่อน
        $roles = Role::whereIn('name', ['teacher', 'student'])
                ->orderByRaw("CASE WHEN name = 'teacher' THEN 1 WHEN name = 'student' THEN 2 END")
                ->get();
        
        // สร้าง collection เพื่อเก็บข้อมูลนักวิจัยแยกตาม role
        $roleUsers = collect();
        $totalResearchers = 0; // ตัวแปรนับจำนวนนักวิจัยทั้งหมดที่พบ
        
        foreach ($roles as $role) {
            // ดึงข้อมูลนักวิจัยตาม role (is_research = 1)
            $users = User::role($role->name)
                ->where('is_research', 1)
                ->with(['expertise', 'program'])
                ->when($search, function ($q) use ($search, $locale) {
                    $q->where(function ($innerQ) use ($search, $locale) {
                        // ค้นหาทั้งภาษาไทยและอังกฤษตามภาษาที่ใช้งานอยู่
                        $innerQ->where('fname_'.$locale, 'LIKE', "%{$search}%")
                               ->orWhere('lname_'.$locale, 'LIKE', "%{$search}%")
                               ->orWhere('fname_en', 'LIKE', "%{$search}%") // ค้นหาในชื่อภาษาอังกฤษเสมอ
                               ->orWhere('lname_en', 'LIKE', "%{$search}%") // ค้นหาในนามสกุลภาษาอังกฤษเสมอ
                               ->orWhere('email', 'LIKE', "%{$search}%")
                               ->orWhere('position_'.$locale, 'LIKE', "%{$search}%") // ค้นหาในตำแหน่งตามภาษา
                               ->orWhereHas('expertise', function ($expertiseQuery) use ($search) {
                                   $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%");
                               })
                               ->orWhereHas('program', function ($programQuery) use ($search, $locale) {
                                   $programQuery->where('program_name_'.$locale, 'LIKE', "%{$search}%");
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
                ->orderBy('fname_'.$locale) // เรียงตามชื่อตามภาษาที่ใช้งาน
                ->get();
            
            // เพิ่มข้อมูลนักวิจัยลงใน collection
            $roleUsers->put($role->id, [
                'role_name' => $role->name,
                'users' => $users
            ]);
            
            // นับจำนวนนักวิจัยที่พบ
            $totalResearchers += $users->count();
        }
        
        // ดึงข้อมูล Visiting Scholars และ Postdoctoral จากตาราง work_of_research_groups พร้อมข้อมูลกลุ่มวิจัย
        $externalResearchers = DB::table('work_of_research_groups as wrg')
            ->join('authors', 'wrg.author_id', '=', 'authors.id')
            ->join('research_groups', 'wrg.research_group_id', '=', 'research_groups.id')
            ->whereIn('wrg.role', [3, 4]) // ดึงทั้ง Postdoctoral (3) และ Visiting Scholar (4)
            ->select(
                'authors.*',
                'wrg.role',
                'research_groups.group_name_en as lab_name',
                'research_groups.id as lab_id',
                DB::raw("CASE 
                    WHEN wrg.role = 3 THEN 'Postdoctoral'
                    WHEN wrg.role = 4 THEN 'Visiting Scholar'
                    END as researcher_type")
            )
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('authors.author_fname', 'LIKE', "%{$search}%")
                      ->orWhere('authors.author_lname', 'LIKE', "%{$search}%")
                      ->orWhere('authors.belong_to', 'LIKE', "%{$search}%")
                      ->orWhere('research_groups.group_name_en', 'LIKE', "%{$search}%")
                      ->orWhere('research_groups.group_name_th', 'LIKE', "%{$search}%");
                });
            })
            ->get();

        // รวมข้อมูลที่ซ้ำกันตามชื่อ
        $externalResearchersCollection = collect();
        
        foreach ($externalResearchers as $researcher) {
            $fullName = $researcher->author_fname . ' ' . $researcher->author_lname;
            
            // ตรวจสอบว่ามีนักวิจัยคนนี้ในคอลเลคชันแล้วหรือไม่
            if ($externalResearchersCollection->has($fullName)) {
                // ถ้ามีแล้ว ให้เพิ่มข้อมูลตำแหน่งและแล็บ
                $existingResearcher = $externalResearchersCollection->get($fullName);
                
                // เพิ่มข้อมูลตำแหน่งและแล็บใหม่
                if (!isset($existingResearcher->positions)) {
                    $existingResearcher->positions = collect();
                }
                
                // เพิ่มตำแหน่งและแล็บใหม่ ตรวจสอบไม่ให้ซ้ำกัน
                $newPosition = [
                    'type' => $researcher->researcher_type,
                    'lab_name' => $researcher->lab_name,
                    'lab_id' => $researcher->lab_id
                ];
                
                // ตรวจสอบว่าตำแหน่งนี้มีอยู่แล้วหรือไม่
                $positionExists = false;
                foreach ($existingResearcher->positions as $position) {
                    if ($position['type'] == $newPosition['type'] && $position['lab_name'] == $newPosition['lab_name']) {
                        $positionExists = true;
                        break;
                    }
                }
                
                // ถ้ายังไม่มี ให้เพิ่มเข้าไป
                if (!$positionExists) {
                    $existingResearcher->positions->push($newPosition);
                }
                
                // อัพเดทข้อมูลในคอลเลคชัน
                $externalResearchersCollection->put($fullName, $existingResearcher);
            } else {
                // ถ้ายังไม่มี ให้เพิ่มนักวิจัยใหม่
                $researcher->positions = collect([
                    [
                        'type' => $researcher->researcher_type,
                        'lab_name' => $researcher->lab_name,
                        'lab_id' => $researcher->lab_id
                    ]
                ]);
                
                $externalResearchersCollection->put($fullName, $researcher);
            }
        }

        // รวม External Researchers ทั้งหมดเข้าด้วยกัน
        if ($externalResearchersCollection->isNotEmpty()) {
            $roleUsers->put('external', [
                'role_name' => 'External',
                'users' => $externalResearchersCollection->values() // แปลงเป็น indexed array
            ]);
            $totalResearchers += $externalResearchersCollection->count();
        }

        // เก็บ ID ของ Role ที่มีนักวิจัย (หลังกรองแล้ว) เพื่อให้ Accordion ขยายได้
        $expandedRoleIds = $roleUsers->filter(function ($item) {
            return $item['users']->isNotEmpty();
        })->keys()->toArray();
        
        // ตรวจสอบว่ามีการค้นหาและไม่พบผลลัพธ์
        $noResults = !empty($search) && $totalResearchers === 0;
    
        return view('researchers.index', compact('roleUsers', 'search', 'expandedRoleIds', 'noResults'));
    }
    

    // แสดงนักวิจัยในโปรแกรมที่ระบุ
    public function program($id, Request $request)
    {
        // รับคำค้นหา
        $search = $request->input('textsearch');
        
        // ตรวจสอบภาษาปัจจุบัน
        $locale = app()->getLocale(); // 'th' หรือ 'en'
    
        // ค้นหานักวิจัยในโปรแกรม (is_research = 1)
        $users = User::where('is_research', 1) // กรองเฉพาะผู้ที่เป็นนักวิจัย (ทุก role)
            ->with(['program', 'expertise', 'roles']) // โหลด roles ด้วย
            ->whereHas('program', fn($q) => $q->where('id', $id))
            ->when($search, fn($q) => $q->where(function ($query) use ($search, $locale) {
                $query->where('fname_'.$locale, 'LIKE', "%{$search}%")
                      ->orWhere('lname_'.$locale, 'LIKE', "%{$search}%")
                      ->orWhere('fname_en', 'LIKE', "%{$search}%") // ค้นหาในชื่อภาษาอังกฤษเสมอ
                      ->orWhere('lname_en', 'LIKE', "%{$search}%") // ค้นหาในนามสกุลภาษาอังกฤษเสมอ
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('position_'.$locale, 'LIKE', "%{$search}%") // ค้นหาในตำแหน่งตามภาษา
                      ->orWhereHas('expertise', fn($expertiseQuery) => $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%"))
                      ->orWhereHas('program', fn($programQuery) => $programQuery->where('program_name_'.$locale, 'LIKE', "%{$search}%"));
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
            ->orderBy('fname_'.$locale) // เรียงตามชื่อตามภาษาที่ใช้งาน
            ->get();
    
        // โหลดข้อมูลโปรแกรม
        $program = Program::findOrFail($id);
        
        // ตรวจสอบว่ามีการค้นหาและไม่พบผลลัพธ์
        $noResults = !empty($search) && $users->isEmpty();
    
        return view('researchers.program', compact('program', 'users', 'search', 'noResults'));
    }
    
}
