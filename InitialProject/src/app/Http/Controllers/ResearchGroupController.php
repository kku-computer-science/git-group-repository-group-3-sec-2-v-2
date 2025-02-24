<?php

namespace App\Http\Controllers;

use App\Models\ResearchGroup;
use App\Models\User;
use App\Models\Fund;
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
        return view('research_groups.create', compact('users', 'funds'));
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
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        // etc. สำหรับฟิลด์ detail, image, ...
        if ($request->hasFile('group_image')) {
            $filename = time() . '.' . $request->file('group_image')->extension();
            $request->file('group_image')->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
        $researchGroup->link = $request->link;
        $researchGroup->save();

        // แนบหัวหน้ากลุ่ม (role=1)
        $owner = Auth::user()->hasAnyRole(['admin', 'staff'])
            ? $request->head
            : Auth::id();
        $researchGroup->user()->attach($owner, [
            'role'     => 1,
            'can_edit' => 1,
        ]);

        // แนบสมาชิก role=2 / 3
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $field) {
                if (!empty($field['userid'])) {
                    $role     = $field['role']     ?? 2;
                    $can_edit = $field['can_edit'] ?? 0;
                    $researchGroup->user()->attach($field['userid'], [
                        'role'     => $role,
                        'can_edit' => $can_edit,
                    ]);
                }
            }
        }

        // นักวิจัยรับเชิญ (ถ้ามีการเก็บในตารางอื่น)
        if ($request->has('visiting')) {
            // เก็บ visiting scholar ตามโครงสร้าง DB จริง
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

        return view('research_groups.edit', compact('researchGroup', 'users'));
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

        // อัปเดตฟิลด์
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        // etc.
        if ($request->hasFile('group_image')) {
            $filename = time() . '.' . $request->file('group_image')->extension();
            $request->file('group_image')->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
        $researchGroup->link = $request->link;
        $researchGroup->save();

        // เก็บ pivot เดิมใน array
        $oldPivot = [];
        foreach ($researchGroup->user as $u) {
            $oldPivot[$u->id] = [
                'role'     => $u->pivot->role,
                'can_edit' => $u->pivot->can_edit,
            ];
        }

        // ลบ pivot เดิม
        $researchGroup->user()->detach();

        // แนบหัวหน้ากลุ่ม (role=1)
        if ($request->filled('head')) {
            $researchGroup->user()->attach($request->head, [
                'role'     => 1,
                'can_edit' => 1,
            ]);
        }

        // แนบสมาชิก
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $field) {
                $userId = $field['userid'] ?? null;
                if (!$userId) continue;

                // Fallback role
                if (isset($field['role'])) {
                    $role = $field['role'];
                } else {
                    $role = $oldPivot[$userId]['role'] ?? 2;
                }

                // Fallback can_edit
                // ถ้ามี can_edit ส่งมา แต่เป็น "" -> ถือว่าไม่มีจริง ให้ใช้ค่าปริยาย
                if (array_key_exists('can_edit', $field)) {
                    if ($field['can_edit'] === '') {
                        // ถ้าเป็น string ว่าง
                        $canEdit = $oldPivot[$userId]['can_edit'] ?? 0;
                    } else {
                        $canEdit = $field['can_edit'];
                    }
                } else {
                    // ถ้าไม่มี can_edit key เลย => fallback ค่าเดิม
                    $canEdit = $oldPivot[$userId]['can_edit'] ?? 0;
                }


                $researchGroup->user()->attach($userId, [
                    'role'     => $role,
                    'can_edit' => $canEdit,
                ]);
            }
        }

        // นักวิจัยรับเชิญ
        if ($request->has('visiting')) {
            // เก็บ visiting scholar
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
