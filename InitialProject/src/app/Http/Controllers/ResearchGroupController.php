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

        // Attach head (role=1)
        $owner = Auth::user()->hasAnyRole(['admin', 'staff'])
            ? $request->head
            : Auth::id();
        $researchGroup->user()->attach($owner, [
            'role'     => 1,
            'can_edit' => 1,
        ]);

        // Attach members (role=2 or 3)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $field) {
                if (!empty($field['userid'])) {
                    $role     = $field['role'] ?? 2;
                    $can_edit = $field['can_edit'] ?? 0;
                    $researchGroup->user()->attach($field['userid'], [
                        'role'     => $role,
                        'can_edit' => $can_edit,
                    ]);
                }
            }
        }

        // Visiting Scholars
        if ($request->has('visiting')) {
            foreach ($request->visiting as $key => $visiting) {
                // กรณีเลือกจาก dropdown ที่มีค่า author_id (และไม่ใช่ manual)
                if (isset($visiting['author_id']) && $visiting['author_id'] !== '' && $visiting['author_id'] !== 'manual') {
                    $author = Author::find($visiting['author_id']);
                    if ($author) {
                        // หากข้อมูลใน form (manual fields) มีการส่งมาด้วยและไม่ตรงกับ DB ให้ update Author
                        $updated = false;
                        if (!empty($visiting['first_name']) && $author->author_fname !== $visiting['first_name']) {
                            $author->author_fname = $visiting['first_name'];
                            $updated = true;
                        }
                        if (!empty($visiting['last_name']) && $author->author_lname !== $visiting['last_name']) {
                            $author->author_lname = $visiting['last_name'];
                            $updated = true;
                        }
                        if (!empty($visiting['affiliation']) && $author->belong_to !== $visiting['affiliation']) {
                            $author->belong_to = $visiting['affiliation'];
                            $updated = true;
                        }
                        // ตรวจสอบไฟล์รูป
                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
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
                        // ผูกความสัมพันธ์กับ pivot role=4
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $author->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    }
                } else {
                    // กรณี "เพิ่มด้วยตัวเอง" (manual)
                    // ลองค้นหา Author โดยอิงจาก first_name และ last_name
                    $existingAuthor = Author::where('author_fname', $visiting['first_name'] ?? '')
                        ->where('author_lname', $visiting['last_name'] ?? '')
                        ->first();
                    if ($existingAuthor) {
                        // หากพบแล้ว ให้ตรวจสอบและอัปเดตข้อมูลที่แตกต่าง
                        $updated = false;
                        if (!empty($visiting['affiliation']) && $existingAuthor->belong_to !== $visiting['affiliation']) {
                            $existingAuthor->belong_to = $visiting['affiliation'];
                            $updated = true;
                        }
                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
                                $destinationPath = public_path('images/imag_user');
                                if (!file_exists($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }
                                $filename = time() . '_' . uniqid() . '.' . $file->extension();
                                $file->move($destinationPath, $filename);
                                $existingAuthor->picture = $filename;
                                $updated = true;
                            }
                        }
                        if ($updated) {
                            $existingAuthor->save();
                        }
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $existingAuthor->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    } else {
                        // หากไม่พบ ให้สร้าง Author ใหม่
                        $newAuthor = new Author();
                        $newAuthor->author_fname = $visiting['first_name'] ?? '';
                        $newAuthor->author_lname = $visiting['last_name'] ?? '';
                        $newAuthor->belong_to    = $visiting['affiliation'] ?? '';

                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
                                $destinationPath = public_path('images/imag_user');
                                if (!file_exists($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }
                                $filename = time() . '_' . uniqid() . '.' . $file->extension();
                                $file->move($destinationPath, $filename);
                                $newAuthor->picture = $filename;
                            }
                        }
                        $newAuthor->save();
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $newAuthor->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    }
                }
            }
        }

        return redirect()->route('researchGroups.index')
            ->with('success', 'Research group created successfully.');
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
    public function update(Request $request, ResearchGroup $researchGroup)
    {
        $request->validate([
            'group_name_th' => 'required',
            'group_name_en' => 'required',
            'link'          => 'nullable|url',
        ]);

        // อัปเดตฟิลด์พื้นฐาน
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

        // เก็บ pivot เดิมสำหรับหัวหน้าและสมาชิกอื่น ๆ
        // (ส่วนนี้ยังคงใช้วิธี detach แล้ว attach ใหม่สำหรับ user pivot)
        $researchGroup->user()->detach();

        // แนบหัวหน้ากลุ่ม (role=1)
        if ($request->filled('head')) {
            $researchGroup->user()->attach($request->head, [
                'role'     => 1,
                'can_edit' => 1,
            ]);
        }

        // แนบสมาชิกกลุ่มวิจัย (role=2/3)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $field) {
                $userId = $field['userid'] ?? null;
                if (!$userId) continue;
                $role     = isset($field['role']) ? $field['role'] : 2;
                $can_edit = array_key_exists('can_edit', $field) && $field['can_edit'] !== '' ? $field['can_edit'] : 0;
                $researchGroup->user()->attach($userId, [
                    'role'     => $role,
                    'can_edit' => $can_edit,
                ]);
            }
        }
        if ($request->has('visiting')) {
            foreach ($request->visiting as $key => $visiting) {
                // กรณีเลือกจาก dropdown ที่มีค่า author_id (และไม่ใช่ manual)
                if (isset($visiting['author_id']) && $visiting['author_id'] !== '' && $visiting['author_id'] !== 'manual') {
                    $author = Author::find($visiting['author_id']);
                    if ($author) {
                        // หากข้อมูลใน form (manual fields) มีการส่งมาด้วยและไม่ตรงกับ DB ให้ update Author
                        $updated = false;
                        if (!empty($visiting['first_name']) && $author->author_fname !== $visiting['first_name']) {
                            $author->author_fname = $visiting['first_name'];
                            $updated = true;
                        }
                        if (!empty($visiting['last_name']) && $author->author_lname !== $visiting['last_name']) {
                            $author->author_lname = $visiting['last_name'];
                            $updated = true;
                        }
                        if (!empty($visiting['affiliation']) && $author->belong_to !== $visiting['affiliation']) {
                            $author->belong_to = $visiting['affiliation'];
                            $updated = true;
                        }
                        // ตรวจสอบไฟล์รูป
                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
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
                        // ผูกความสัมพันธ์กับ pivot role=4
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $author->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    }
                } else {
                    // กรณี "เพิ่มด้วยตัวเอง" (manual)
                    // ลองค้นหา Author โดยอิงจาก first_name และ last_name
                    $existingAuthor = Author::where('author_fname', $visiting['first_name'] ?? '')
                        ->where('author_lname', $visiting['last_name'] ?? '')
                        ->first();
                    if ($existingAuthor) {
                        // หากพบแล้ว ให้ตรวจสอบและอัปเดตข้อมูลที่แตกต่าง
                        $updated = false;
                        if (!empty($visiting['affiliation']) && $existingAuthor->belong_to !== $visiting['affiliation']) {
                            $existingAuthor->belong_to = $visiting['affiliation'];
                            $updated = true;
                        }
                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
                                $destinationPath = public_path('images/imag_user');
                                if (!file_exists($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }
                                $filename = time() . '_' . uniqid() . '.' . $file->extension();
                                $file->move($destinationPath, $filename);
                                $existingAuthor->picture = $filename;
                                $updated = true;
                            }
                        }
                        if ($updated) {
                            $existingAuthor->save();
                        }
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $existingAuthor->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    } else {
                        // หากไม่พบ ให้สร้าง Author ใหม่
                        $newAuthor = new Author();
                        $newAuthor->author_fname = $visiting['first_name'] ?? '';
                        $newAuthor->author_lname = $visiting['last_name'] ?? '';
                        $newAuthor->belong_to    = $visiting['affiliation'] ?? '';

                        if ($request->hasFile("visiting.$key.picture")) {
                            $file = $request->file("visiting.$key.picture");
                            if ($file->isValid()) {
                                $destinationPath = public_path('images/imag_user');
                                if (!file_exists($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }
                                $filename = time() . '_' . uniqid() . '.' . $file->extension();
                                $file->move($destinationPath, $filename);
                                $newAuthor->picture = $filename;
                            }
                        }
                        $newAuthor->save();
                        $researchGroup->visitingScholars()->syncWithoutDetaching([
                            $newAuthor->id => ['role' => 4, 'can_edit' => 0]
                        ]);
                    }
                }
            }
        }

        return redirect()->route('researchGroups.index')
            ->with('success', 'Research group updated successfully');
    }


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
