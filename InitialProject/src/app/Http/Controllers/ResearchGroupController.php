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

<<<<<<< HEAD
        $researchGroups = ResearchGroup::whereHas('user', function ($query) {
            $query->where('user_id', Auth::id());
        })->get();
=======
>>>>>>> origin/main
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
<<<<<<< HEAD
        // $input['group_image'] = time().'.'.$request->group_image->extension();
        // $request->group_image->move(public_path('img'), $input['group_image']);
        //return $input['group_image'];

        $researchGroup = ResearchGroup::create($input);
        $head = $request->head;
        $fund = $request->fund;
        $researchGroup->user()->attach($head, ['role' => 1]);
        if ($request->moreFields) {
            foreach ($request->moreFields as $key => $value) {

                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value, ['role' => 2]);
                }
            }
        }
        if ($request->postdoctoral) {
            foreach ($request->postdoctoral as $key => $value) {
                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value['userid'], ['role' => 3]);
                }
            }
        }
        
        if ($request->students) {
            foreach ($request->students as $key => $value) {
                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value['userid'], ['role' => 5]);
                }
            }
        }
        return redirect()->route('researchGroups.index')->with('success', 'research group created successfully.');
=======
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
>>>>>>> origin/main
    }
    

<<<<<<< HEAD
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
=======
>>>>>>> origin/main
    public function update(Request $request, ResearchGroup $researchGroup)
    {
        $request->validate([
            'group_name_th' => 'required',
            'group_name_en' => 'required',
            'link'          => 'nullable|url',
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
            $filename = time() . '.' . $request->file('group_image')->extension();
            $request->file('group_image')->move(public_path('img'), $filename);
            $researchGroup->group_image = $filename;
        }
<<<<<<< HEAD
        $researchGroup->update($input);
        $researchGroup->user()->detach();

        $head = $request->head;
        $researchGroup->user()->attach(array(
            $head => array('role' => 1),
        ));

        if ($request->moreFields) {
            foreach ($request->moreFields as $key => $value) {

                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value, ['role' => 2]);
                }
            }
        }
        if ($request->postdoctoral) {
            foreach ($request->postdoctoral as $key => $value) {
                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value, ['role' => 3]);
                }
            }
        }
        
        if ($request->students) {
            foreach ($request->students as $key => $value) {
                if ($value['userid'] != null) {
                    $researchGroup->user()->attach($value['userid'], ['role' => 5]);
                }
            }
        }
=======
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
                        'role' => 2,
                        'can_edit' => $member['can_edit']
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
    
>>>>>>> origin/main
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
