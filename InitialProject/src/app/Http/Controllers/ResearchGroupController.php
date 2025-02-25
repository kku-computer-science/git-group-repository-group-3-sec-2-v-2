<?php

namespace App\Http\Controllers;

use App\Models\ResearchGroup;
use App\Models\User;
use App\Models\Fund;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $filename = time() . '.' . $request->file('group_image')->extension();
            $request->file('group_image')->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
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
                        'role'     => 2,
                        'can_edit' => $member['can_edit']
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
                    $newVisiting[$author->id] = ['role' => 4, 'can_edit' => 0];
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
        ]);
    
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_detail_th = $request->group_detail_th;
        $researchGroup->group_detail_en = $request->group_detail_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        $researchGroup->group_main_research_en = $request->group_main_research_en;
        $researchGroup->group_main_research_th = $request->group_main_research_th;
    
        if ($request->hasFile('group_image')) {
            $filename = time() . '.' . $request->file('group_image')->extension();
            $request->file('group_image')->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
        $researchGroup->link = $request->link;
        $researchGroup->save();
    
        // ประมวลผลข้อมูล member จากฟอร์ม (head + member อื่นๆ)
        $membersPivot = [];
    
        // สำหรับ head (ในฟอร์มจะแยกกันส่ง head ออกมา)
        if (auth()->user()->hasAnyRole(['admin','staff'])) {
            $headUserId = $request->head;
        } else {
            $headUserId = auth()->id();
        }
        // กำหนดให้ head มี role=1 และ can_edit=1
        $membersPivot[$headUserId] = ['role' => 1, 'can_edit' => 1];
    
        // ประมวลผลข้อมูล member ที่ถูกส่งมาจากฟอร์ม (moreFields)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $member) {
                if (isset($member['userid']) && !empty($member['userid'])) {
                    $membersPivot[$member['userid']] = [
                        'role' => 2,
                        'can_edit' => $member['can_edit']
                    ];
                }
            }
        }
        // sync ความสัมพันธ์ของ users กับ researchGroup โดยอัปเดต pivot table
        $researchGroup->user()->sync($membersPivot);
    
        // ส่วนของ Visiting Scholars (แก้ไขการ sync ตามที่แนะนำไว้ก่อนหน้า)
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
                    $author->belong_to = $visiting['affiliation'] ?? '';
    
                    if ($request->hasFile("visiting.$key.picture")) {
                        $file = $request->file("visiting.$key.picture");
                        $filename = time() . '_' . uniqid() . '.' . $file->extension();
                        $file->move(public_path('images/imag_user'), $filename);
                        $author->picture = $filename;
                    }
    
                    $author->save();
    
                    $newVisiting[$author->id] = ['role' => 4, 'can_edit' => 0];
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
    public function edit(ResearchGroup $researchGroup)
    {
        // เช็ค Policy/permission
        $this->authorize('update', $researchGroup);

        // โหลดความสัมพันธ์ user ด้วย pivot
        $researchGroup->load('user');
        $users = User::all();
        $authors = Author::all(); // ดึงข้อมูลนักวิจัยรับเชิญจากตาราง Author

        return view('research_groups.edit', compact('researchGroup', 'users', 'authors'));
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
