<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ResearchGroup;
use Illuminate\Http\Request;
use App\Models\Fund;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ResearchGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:groups-list|groups-create|groups-edit|groups-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:groups-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:groups-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:groups-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'staff'])) {
            // หากผู้ใช้เป็น admin หรือ staff ให้แสดงกลุ่มทั้งหมด
            $researchGroups = ResearchGroup::with('user')
                ->orderBy('group_name_en')
                ->get();
        } else {
            // หากไม่ใช่ admin หรือ staff ให้แสดงเฉพาะกลุ่มที่มีผู้ใช้ปัจจุบันเข้าร่วม
            $researchGroups = ResearchGroup::whereHas('user', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('user')
            ->orderBy('group_name_en')
            ->get();
        }

        return view('research_groups.index', compact('researchGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::role(['teacher', 'student'])->get();
        $funds = Fund::get();
        return view('research_groups.create', compact('users', 'funds'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name_th' => 'required',
            'group_name_en' => 'required',
            'head'          => 'required',
            'link'          => 'nullable|url',  // validate รูปแบบ URL หากมี
            // กำหนด validate อื่น ๆ ตามที่ต้องการ
        ]);
    
        // สร้าง instance ใหม่ของ ResearchGroup และกำหนดค่าต่างๆ
        $researchGroup = new ResearchGroup();
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        $researchGroup->group_detail_th = $request->group_detail_th;
        $researchGroup->group_detail_en = $request->group_detail_en;
    
        // ตรวจสอบและจัดการกับไฟล์ image หากมี
        if ($request->hasFile('group_image')) {
            $filename = time() . '.' . $request->group_image->extension();
            $request->group_image->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
    
        // กำหนดค่า link จาก request แบบ manual
        $researchGroup->link = $request->link;
    
        // บันทึกข้อมูลลงในฐานข้อมูล
        $researchGroup->save();
    
        // กำหนดเจ้าของกลุ่ม (Owner) โดยพิจารณาจาก role ของผู้ใช้ที่ล็อกอิน
        // หากผู้ใช้มี role เป็น admin หรือ staff ให้ใช้ค่าจากฟอร์ม (head)
        // แต่ถ้าไม่ใช่ ให้ใช้ auth()->id() เป็นเจ้าของกลุ่ม
        $owner = auth()->user()->hasAnyRole(['admin', 'staff']) ? $request->head : auth()->id();
        $researchGroup->user()->attach($owner, ['role' => 1]);
    
        // แนบสมาชิกกลุ่ม (role = 2) หากมีข้อมูลใน moreFields
        if ($request->moreFields) {
            foreach ($request->moreFields as $field) {
                if (isset($field['userid']) && $field['userid'] != null) {
                    $researchGroup->user()->attach($field['userid'], ['role' => 2]);
                }
            }
        }
    
        // แนบ Postdoctoral Researcher (role = 3) หากมีข้อมูลใน postdocFields
        if ($request->has('postdocFields')) {
            foreach ($request->postdocFields as $field) {
                if (isset($field['userid']) && $field['userid'] != null) {
                    $researchGroup->user()->attach($field['userid'], ['role' => 3]);
                }
            }
        }
    
        return redirect()->route('researchGroups.index')->with('success', 'research group created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function show(ResearchGroup $researchGroup)
    {
        #$researchGroup=ResearchGroup::find($researchGroup->id);
        //dd($researchGroup->id);
        //$data=ResearchGroup::find($researchGroup->id)->get(); 

        //return $data;
        return view('research_groups.show', compact('researchGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(ResearchGroup $researchGroup)
    {
        $researchGroup = ResearchGroup::find($researchGroup->id);
        $this->authorize('update', $researchGroup);
        $researchGroup = ResearchGroup::with(['user'])->where('id', $researchGroup->id)->first();
        $users = User::get();
        //return $users;
        return view('research_groups.edit', compact('researchGroup', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResearchGroup  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResearchGroup $researchGroup)
    {
        $request->validate([
            'group_name_th'   => 'required',
            'group_name_en'   => 'required',
            'link'            => 'nullable|url',  // ตรวจสอบรูปแบบ URL
            // เพิ่มเติม validation อื่น ๆ ตามต้องการ
        ]);
    
        // อัปเดตฟิลด์หลัก ๆ
        $researchGroup->group_name_th   = $request->group_name_th;
        $researchGroup->group_name_en   = $request->group_name_en;
        $researchGroup->group_desc_th   = $request->group_desc_th;
        $researchGroup->group_desc_en   = $request->group_desc_en;
        $researchGroup->group_detail_th = $request->group_detail_th;
        $researchGroup->group_detail_en = $request->group_detail_en;
    
        // จัดการรูปภาพ (ถ้ามีอัปโหลด)
        if ($request->hasFile('group_image')) {
            $filename = time() . '.' . $request->group_image->extension();
            $request->group_image->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
    
        // กำหนดค่า link
        $researchGroup->link = $request->link;
        $researchGroup->save();
    
        // ลบ pivot เดิมทั้งหมดก่อน (ถ้าไม่ต้องการเก็บข้อมูลเดิมใด ๆ ไว้)
        $researchGroup->user()->detach();
    
        // 1) หัวหน้ากลุ่ม (role=1)
        if ($request->filled('head')) {
            $researchGroup->user()->attach($request->head, ['role' => 1]);
        }
    
        // 2) สมาชิกกลุ่ม (role=2)
        if ($request->has('moreFields')) {
            foreach ($request->moreFields as $field) {
                if (!empty($field['userid'])) {
                    $researchGroup->user()->attach($field['userid'], ['role' => 2]);
                }
            }
        }
    
        // 3) Postdoctoral (role=3) — **เพิ่มส่วนนี้**
        if ($request->has('postdocFields')) {
            foreach ($request->postdocFields as $field) {
                if (!empty($field['userid'])) {
                    // attach user_id => role=3
                    $researchGroup->user()->attach($field['userid'], ['role' => 3]);
                }
            }
        }
    
        return redirect()->route('researchGroups.index')
                         ->with('success', 'Research group updated successfully');
    }
    


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResearchGroup $researchGroup)
    {
        $this->authorize('delete', $researchGroup);
        $researchGroup->delete();
        return redirect()->route('researchGroups.index')
            ->with('success', 'researchGroups deleted successfully');
    }
}
