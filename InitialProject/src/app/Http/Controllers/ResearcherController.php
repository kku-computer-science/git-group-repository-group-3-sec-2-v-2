<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ResearcherController extends Controller
{
    // แสดงรายชื่อนักวิจัยทั้งหมดแยกตาม role
    public function index(Request $request)
    {
        $search = $request->input('textsearch');
        $locale = app()->getLocale();
        $perPage = 8;

        $roles = Role::whereIn('name', ['teacher', 'student'])
                ->orderByRaw("CASE WHEN name = 'teacher' THEN 1 WHEN name = 'student' THEN 2 END")
                ->get();

        $roleUsers = collect();
        $totalResearchers = 0;
        
        foreach ($roles as $role) {
            $pageName = $role->name . '_page';
            $currentPage = (int) $request->input($pageName, 1);
            
            $query = User::role($role->name)
                ->where('is_research', 1)
                ->with(['expertise', 'program'])
                ->when($search, function ($q) use ($search, $locale) {
                    $q->where(function ($innerQ) use ($search, $locale) {
                        $innerQ->where('fname_'.$locale, 'LIKE', "%{$search}%")
                               ->orWhere('lname_'.$locale, 'LIKE', "%{$search}%")
                               ->orWhere('fname_en', 'LIKE', "%{$search}%")
                               ->orWhere('lname_en', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%")
                               ->orWhere('position_'.$locale, 'LIKE', "%{$search}%")
                               ->orWhereHas('expertise', function ($expertiseQuery) use ($search) {
                                   $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%");
                               })
                               ->orWhereHas('program', function ($programQuery) use ($search, $locale) {
                                   $programQuery->where('program_name_'.$locale, 'LIKE', "%{$search}%");
                               });
                    });
                })
                ->orderByRaw("
                    FIELD(position_en,
                        'Prof. Dr.',
                        'Assoc. Prof. Dr.',
                        'Asst. Prof. Dr.',
                        'Assoc. Prof.',
                        'Asst. Prof.',
                        'Lecturer')
                ")
                ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
                ->orderBy('fname_'.$locale);

            $paginatedUsers = $query->paginate($perPage, ['*'], $pageName, $currentPage)->withQueryString();

            $totalUsers = $paginatedUsers->total();

            $roleUsers->put($role->id, [
                'role_name' => $role->name,
                'users' => $paginatedUsers,
                'total_users' => $totalUsers,
            ]);

            $totalResearchers += $totalUsers;
        }
        
        $externalResearchers = DB::table('work_of_research_groups as wrg')
            ->join('authors', 'wrg.author_id', '=', 'authors.id')
            ->join('research_groups', 'wrg.research_group_id', '=', 'research_groups.id')
            ->whereIn('wrg.role', [3, 4])
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

        $externalResearchersCollection = collect();
        
        foreach ($externalResearchers as $researcher) {
            $fullName = $researcher->author_fname . ' ' . $researcher->author_lname;
            
            if ($externalResearchersCollection->has($fullName)) {
                $existingResearcher = $externalResearchersCollection->get($fullName);
                
                if (!isset($existingResearcher->positions)) {
                    $existingResearcher->positions = collect();
                }
                
                $newPosition = [
                    'type' => $researcher->researcher_type,
                    'lab_name' => $researcher->lab_name,
                    'lab_id' => $researcher->lab_id
                ];
                
                $positionExists = false;
                foreach ($existingResearcher->positions as $position) {
                    if ($position['type'] == $newPosition['type'] && $position['lab_name'] == $newPosition['lab_name']) {
                        $positionExists = true;
                        break;
                    }
                }
                
                if (!$positionExists) {
                    $existingResearcher->positions->push($newPosition);
                }
                
                $externalResearchersCollection->put($fullName, $existingResearcher);
            } else {
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

        if ($externalResearchersCollection->isNotEmpty()) {
            $currentPage = (int) $request->input('external_page', 1);
            $paginatedExternalResearchers = new LengthAwarePaginator(
                $externalResearchersCollection->values()->forPage($currentPage, $perPage)->values(),
                $externalResearchersCollection->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'external_page',
                    'query' => $request->query(),
                ]
            );

            $roleUsers->put('external', [
                'role_name' => 'External',
                'users' => $paginatedExternalResearchers,
                'total_users' => $externalResearchersCollection->count(),
            ]);
            $totalResearchers += $externalResearchersCollection->count();
        }

        $expandedRoleIds = $roleUsers->filter(function ($item) {
            return $item['total_users'] > 0;
        })->keys()->toArray();
        
        $noResults = !empty($search) && $totalResearchers === 0;

        // Programs list for sidebar filter
        $programs = Program::orderBy('program_name_en')->get(['id', 'program_name_en', 'program_name_th']);
    
        return view('researchers.index', compact('roleUsers', 'search', 'expandedRoleIds', 'noResults', 'programs'));
    }

    

    // แสดงนักวิจัยในโปรแกรมที่ระบุ
    public function program($id, Request $request)
    {
        $search = $request->input('textsearch');
        $locale = app()->getLocale();

        $users = User::where('is_research', 1) // กรองเฉพาะผู้ที่เป็นนักวิจัย (ทุก role)
            ->with(['program', 'expertise', 'roles']) // โหลด roles ด้วย
            ->whereHas('program', fn($q) => $q->where('id', $id))
            ->when($search, fn($q) => $q->where(function ($query) use ($search, $locale) {
                $query->where('fname_'.$locale, 'LIKE', "%{$search}%")
                      ->orWhere('lname_'.$locale, 'LIKE', "%{$search}%")
                      ->orWhere('fname_en', 'LIKE', "%{$search}%")
                      ->orWhere('lname_en', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('position_'.$locale, 'LIKE', "%{$search}%")
                      ->orWhereHas('expertise', fn($expertiseQuery) => $expertiseQuery->where('expert_name', 'LIKE', "%{$search}%"))
                      ->orWhereHas('program', fn($programQuery) => $programQuery->where('program_name_'.$locale, 'LIKE', "%{$search}%"));
            }))
            ->orderByRaw("
                FIELD(position_en,
                    'Prof. Dr.',
                    'Assoc. Prof. Dr.',
                    'Asst. Prof. Dr.',
                    'Assoc. Prof.',
                    'Asst. Prof.',
                    'Lecturer')
            ")
            ->orderByRaw("IF(doctoral_degree = 'Ph.D.', 0, 1)")
            ->orderBy('fname_'.$locale)
            ->paginate(12)
            ->withQueryString();

        $program = Program::findOrFail($id);

        $noResults = !empty($search) && $users->total() === 0;
    
        return view('researchers.program', compact('program', 'users', 'search', 'noResults'));
    }
    
}
