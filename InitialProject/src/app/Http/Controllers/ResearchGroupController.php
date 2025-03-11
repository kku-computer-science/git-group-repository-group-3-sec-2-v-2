<?php

namespace App\Http\Controllers;

use App\Models\ResearchGroup;
use App\Models\User;
use App\Models\Fund;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResearchGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:groups-list|groups-create|groups-edit|groups-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:groups-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:groups-edit',   ['only' => ['edit', 'update']]);
        $this->middleware('permission:groups-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['admin', 'staff'])) {
            $researchGroups = ResearchGroup::with('user')->orderBy('group_name_en')->get();
        } else {
            // คนทั่วไป เห็นเฉพาะกลุ่มที่ตัวเองอยู่
            $researchGroups = ResearchGroup::whereHas('user', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->with('user')
                ->orderBy('group_name_en')
                ->get();
        }

        return view('research_groups.index', compact('researchGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $funds = Fund::all(); // ถ้ามีตาราง Fund
        $authors = Author::all(); // ดึงข้อมูลนักวิจัยรับเชิญจากตาราง Author
        return view('research_groups.create', compact('users', 'funds', 'authors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name_th' => 'required',
            'group_name_en' => 'required',
            'head'          => 'required',
            'link'          => 'nullable|url',
            'group_image'     => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'visiting.*.picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
    
        $researchGroup = new ResearchGroup();
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_detail_th = $request->group_detail_th;
        $researchGroup->group_detail_en = $request->group_detail_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        $researchGroup->group_main_research_en = $request->group_main_research_en;
        $researchGroup->group_main_research_th = $request->group_main_research_th;
    
        if ($request->hasFile('group_image')) {
            $file = $request->file('group_image');
            // ตรวจสอบว่าไฟล์มี MIME type ที่เริ่มต้นด้วย "image/"
            if ($file->isValid() && strpos($file->getMimeType(), 'image/') === 0) {
                $filename = time() . '.' . $file->extension();
                $file->move(public_path('img'), $filename);
                $researchGroup->group_image = $filename;
            } else {
                // หากไฟล์ไม่ใช่รูปภาพ ให้ส่ง error กลับไป
                return redirect()->back()->withErrors(['group_image' => 'Uploaded file must be an image.']);
            }
        }
        
        $researchGroup->link = $request->link;
        $researchGroup->save();
    
        // ประมวลผลข้อมูลของสมาชิก (head + additional members)
        $membersPivot = [];
        if (auth()->user()->hasAnyRole(['admin', 'staff'])) {
            $headUserId = $request->head;
        } else {
            $headUserId = auth()->id();
        }
        // กำหนด head ให้มี role=1 และ can_edit=1
        $membersPivot[$headUserId] = ['role' => 1, 'can_edit' => 1];
    
        // ประมวลผลสมาชิกกลุ่มวิจัยที่ส่งมาจากฟอร์ม (moreFields)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $member) {
                if (isset($member['userid']) && !empty($member['userid'])) {
                    $membersPivot[$member['userid']] = [
                        'role' => $member['role'] ?? 2,
                        'can_edit' => $member['can_edit'] ?? 0
                    ];
                }
            }
        }
        // sync ความสัมพันธ์ของสมาชิกใน pivot table
        $researchGroup->user()->sync($membersPivot);
    
        // ประมวลผลข้อมูล Visiting Scholars
        if ($request->has('visiting')) {
            $newVisiting = [];
            foreach ($request->visiting as $key => $visiting) {
                if (
                    isset($visiting['first_name']) && trim($visiting['first_name']) !== '' &&
                    isset($visiting['last_name']) && trim($visiting['last_name']) !== ''
                ) {
                    if (isset($visiting['author_id']) && $visiting['author_id'] !== '' && $visiting['author_id'] !== 'manual') {
                        $author = Author::find($visiting['author_id']);
                    } else {
                        $author = Author::where('author_fname', $visiting['first_name'])
                            ->where('author_lname', $visiting['last_name'])
                            ->first();
                        if (!$author) {
                            $author = new Author();
                        }
                    }
    
                    $updated = false;
                    if ($author->author_fname !== $visiting['first_name']) {
                        $author->author_fname = $visiting['first_name'];
                        $updated = true;
                    }
                    if ($author->author_lname !== $visiting['last_name']) {
                        $author->author_lname = $visiting['last_name'];
                        $updated = true;
                    }
                    if (isset($visiting['affiliation']) && $author->belong_to !== $visiting['affiliation']) {
                        $author->belong_to = $visiting['affiliation'];
                        $updated = true;
                    }
                    if ($request->hasFile("visiting.$key.picture")) {
                        $file = $request->file("visiting.$key.picture");
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        if ($file->isValid() && in_array(strtolower($file->extension()), $allowedExtensions)) {
                            $destinationPath = public_path('images/imag_user');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $filename = time() . '_' . uniqid() . '.' . $file->extension();
                            $file->move($destinationPath, $filename);
                            $author->picture = $filename;
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        $author->save();
                    }
                    $newVisiting[$author->id] = ['role' => $visiting['role'] ?? 4, 'can_edit' => 0];
                } else {
                    return redirect()->back()->withErrors(['error' => 'First name and last name are required.']);
                }
            }
            $researchGroup->visitingScholars()->sync($newVisiting);
        }
    
        return redirect()->route('researchGroups.index')
            ->with('success', 'Research group created successfully.');
    }
    

    public function update(Request $request, ResearchGroup $researchGroup)
    {
        $request->validate([
            'group_name_th' => 'required',
            'group_name_en' => 'required',
            'link'          => 'nullable|url',
            'group_image'     => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'visiting.*.picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
    
        // อัปเดตข้อมูลพื้นฐานของกลุ่มวิจัย
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_detail_th = $request->group_detail_th;
        $researchGroup->group_detail_en = $request->group_detail_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        $researchGroup->group_main_research_en = $request->group_main_research_en;
        $researchGroup->group_main_research_th = $request->group_main_research_th;
    
        if ($request->hasFile('group_image')) {
            $file = $request->file('group_image');
            // ตรวจสอบว่าไฟล์มี MIME type ที่เริ่มต้นด้วย "image/"
            if ($file->isValid() && strpos($file->getMimeType(), 'image/') === 0) {
                $filename = time() . '.' . $file->extension();
                $file->move(public_path('img'), $filename);
                $researchGroup->group_image = $filename;
            } else {
                // หากไฟล์ไม่ใช่รูปภาพ ให้ส่ง error กลับไป
                return redirect()->back()->withErrors(['group_image' => 'Uploaded file must be an image.']);
            }
        }
        
        $researchGroup->link = $request->link;
        $researchGroup->save();
    
        // ดึง Head Lab เดิมจากฐานข้อมูล
        $originalHead = $researchGroup->user()->wherePivot('role', 1)->first();
    
        // ประมวลผลข้อมูล member จากฟอร์ม
        $membersPivot = [];
    
        if (auth()->user()->hasAnyRole(['admin', 'staff'])) {
            // Admin หรือ Staff สามารถเปลี่ยน Head Lab ได้
            $headUserId = $request->head;
        } else {
            // ผู้ใช้ทั่วไป ไม่สามารถเปลี่ยน Head Lab ได้ ใช้ Head Lab เดิม
            $headUserId = $originalHead ? $originalHead->id : auth()->id();
        }
        // กำหนดให้ Head Lab มี role=1 และ can_edit=1
        $membersPivot[$headUserId] = ['role' => 1, 'can_edit' => 1];
    
        // ประมวลผลสมาชิกจากฟอร์ม (moreFields)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $member) {
                if (isset($member['userid']) && !empty($member['userid'])) {
                    $membersPivot[$member['userid']] = [
                        'role' => $member['role'] ?? 2,
                        'can_edit' => $member['can_edit'] ?? 0
                    ];
                }
            }
        }
    
        // sync ความสัมพันธ์ของ users กับ researchGroup
        $researchGroup->user()->sync($membersPivot);
    
        // ส่วนของ Visiting Scholars
        if ($request->has('visiting')) {
            $newVisiting = [];
            foreach ($request->visiting as $key => $visiting) {
                if (
                    isset($visiting['first_name']) && trim($visiting['first_name']) !== '' &&
                    isset($visiting['last_name']) && trim($visiting['last_name']) !== ''
                ) {
                    $author = Author::find($visiting['author_id']) ?? new Author();
                    $author->author_fname = $visiting['first_name'];
                    $author->author_lname = $visiting['last_name'];
                    $author->belong_to = $visiting['affiliation'] ?? null;
                    $author->doctoral_degree = $visiting['doctoral_degree'] ?? null;
                    $author->academic_ranks_en = $visiting['academic_ranks_en'] ?? null;
                    $author->academic_ranks_th = $visiting['academic_ranks_th'] ?? null;
    
                    if ($request->hasFile("visiting.$key.picture")) {
                        $file = $request->file("visiting.$key.picture");
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        if ($file->isValid() && in_array(strtolower($file->extension()), $allowedExtensions)) {
                            $destinationPath = public_path('images/imag_user');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $filename = time() . '_' . uniqid() . '.' . $file->extension();
                            $file->move($destinationPath, $filename);
                            $author->picture = $filename;
                        }
                    }
                    
                    $author->save();
                    $newVisiting[$author->id] = ['role' => $visiting['role'] ?? 4, 'can_edit' => 0];
                } else {
                    return redirect()->back()->withErrors(['error' => 'First name and last name are required.']);
                }
            }
            $researchGroup->visitingScholars()->sync($newVisiting);
        } else {
            $researchGroup->visitingScholars()->detach();
        }
    
        return redirect()->route('researchGroups.index')
            ->with('success', 'Research group updated successfully.');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(ResearchGroup $researchGroup)
    {
        // $researchGroup => model binding
        return view('research_groups.show', compact('researchGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $researchGroup = ResearchGroup::with(['user', 'visitingScholars'])->findOrFail($id);
        
        // ตรวจสอบว่ามีข้อมูล visiting scholars ที่มี role=3 (Postdoctoral) แต่ไม่มีในข้อมูลที่ถูกโหลด
        // เนื่องจากบางกรณี Postdoctoral ที่มี user_id=null อาจไม่ถูกดึงมาในความสัมพันธ์ visitingScholars
        $missingPostdocs = DB::table('work_of_research_groups')
            ->where('research_group_id', $id)
            ->where('role', 3)
            ->whereNull('user_id')
            ->whereNotNull('author_id')
            ->get();
            
        // ถ้ามีข้อมูลที่หายไป ให้เพิ่มเข้าไปใน $researchGroup->visitingScholars
        if ($missingPostdocs->isNotEmpty()) {
            $missingAuthorIds = $missingPostdocs->pluck('author_id')->toArray();
            $missingAuthors = Author::whereIn('id', $missingAuthorIds)->get();
            
            // เพิ่มข้อมูล pivot สำหรับแต่ละ author ที่หายไป
            foreach ($missingAuthors as $author) {
                $pivotData = $missingPostdocs->firstWhere('author_id', $author->id);
                
                // สร้าง stdClass object เพื่อจำลอง Pivot model
                $pivot = new \stdClass();
                $pivot->role = $pivotData->role;
                $pivot->can_edit = $pivotData->can_edit;
                $pivot->user_id = null;
                $pivot->research_group_id = $id;
                $pivot->author_id = $author->id;
                
                // กำหนด pivot property ให้กับ author
                $author->pivot = $pivot;
                
                // เพิ่ม author ที่หายไปเข้าสู่ collection ของ visitingScholars
                $researchGroup->visitingScholars->push($author);
            }
        }
        
        $users = User::all();
        $funds = Fund::all();
        $authors = Author::all();

        // ดึงข้อมูล Postdoctoral จาก users และ authors
        $postdoctoralUsers = $researchGroup->user()->wherePivot('role', 3)->get();
        $postdoctoralAuthors = $researchGroup->visitingScholars()->wherePivot('role', 3)->get();

        // รวมข้อมูล Postdoctoral จากทั้งสองแหล่ง
        $postdoctorals = $postdoctoralUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'type' => 'user',
                'first_name' => $user->fname_en,
                'last_name' => $user->lname_en,
                'affiliation' => $user->organization
            ];
        })->concat($postdoctoralAuthors->map(function ($author) {
            return [
                'id' => $author->id,
                'type' => 'author',
                'first_name' => $author->author_fname,
                'last_name' => $author->author_lname,
                'affiliation' => $author->belong_to
            ];
        }));

        return view('research_groups.edit', compact('researchGroup', 'users', 'funds', 'authors', 'postdoctorals'));
    }

    /**
     * Update the specified resource in storage.
     * หลักการ: ถ้าไม่มีส่ง can_edit มา => ใช้ค่าเก่าจาก pivot
     */



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResearchGroup $researchGroup)
    {
        $this->authorize('delete', $researchGroup);
        $researchGroup->delete();

        return redirect()->route('researchGroups.index')
            ->with('success', 'researchGroups deleted successfully');
    }
}
