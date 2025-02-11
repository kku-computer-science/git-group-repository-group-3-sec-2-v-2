<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use DB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use File;

class UserController extends Controller
{
    /**
     * Create a new instance of the class.
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store']]);
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::all();
        return view('users.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        $departments = Department::all();
        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Get sub-categories (programs) based on the department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response (JSON)
     */
    public function getCategory(Request $request)
    {
        $cat = $request->cat_id;
        // คุณอาจแก้ไขให้ใช้ $cat ในเงื่อนไข where ได้ตามต้องการ
        $subcat = Program::with('degree')->where('department_id', 1)->get();
        return response()->json($subcat);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate ฟิลด์ที่จำเป็น (is_research ไม่จำเป็นต้อง validate เพราะเป็นตัวเลือก)
        $this->validate($request, [
            'fname_en'  => 'required',
            'lname_en'  => 'required',
            'fname_th'  => 'required',
            'lname_th'  => 'required',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed',
            'roles'     => 'required',
            'sub_cat'   => 'required',
        ]);

        // สร้างผู้ใช้งานใหม่ โดยเพิ่มฟิลด์ is_research
        $user = User::create([
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'fname_en'    => $request->fname_en,
            'lname_en'    => $request->lname_en,
            'fname_th'    => $request->fname_th,
            'lname_th'    => $request->lname_th,
            'is_research' => $request->has('is_research') ? 1 : 0, // ถ้า checkbox ถูกเลือกจะได้ 1, มิฉะนั้น 0
        ]);

        // กำหนด role ให้กับผู้ใช้งาน
        $user->assignRole($request->roles);

        // Associate ผู้ใช้งานกับโปรแกรมที่เลือก
        $pro_id = $request->sub_cat;
        $program = Program::find($pro_id);
        $user->program()->associate($program);
        $user->save();

        return redirect()->route('users.index')
                         ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $departments = Department::all();
        $depId = $user->program->department_id;
        $programs = Program::whereHas('department', function($q) use ($depId){    
            $q->where('id', '=', $depId);
        })->get();
        
        $roles = Role::pluck('name', 'name')->all();
        $deps = Department::pluck('department_name_EN','department_name_EN')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        $userDep = $user->department()->pluck('department_name_EN','department_name_EN')->all();
        return view('users.edit', compact('user', 'roles', 'deps', 'userRole', 'userDep', 'programs', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate ฟิลด์ที่จำเป็น
        $this->validate($request, [
            'fname_en'  => 'required',
            'fname_th'  => 'required',
            'lname_en'  => 'required',
            'lname_th'  => 'required',
            'email'     => 'required|email|unique:users,email,'.$id,
            'password'  => 'confirmed',
            'roles'     => 'required'
        ]);

        $input = $request->all();

        // เพิ่มการอัปเดตฟิลด์ is_research
        $input['is_research'] = $request->has('is_research') ? 1 : 0;

        if (!empty($input['password'])) { 
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);    
        }
    
        $user = User::find($id);
        $user->update($input);

        // ลบ role เก่าและกำหนด role ใหม่ให้กับผู้ใช้งาน
        DB::table('model_has_roles')
            ->where('model_id', $id)
            ->delete();
    
        $user->assignRole($request->input('roles'));

        // Associate ผู้ใช้งานกับโปรแกรมที่เลือก
        $pro_id = $request->sub_cat;
        $program = Program::find($pro_id);
        $user->program()->associate($program);
        $user->save();

        return redirect()->route('users.index')
                         ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                         ->with('success', 'User deleted successfully.');
    }

    /**
     * Display the user's profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('dashboards.users.profile');
    }

    /**
     * Update the user's profile picture.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response (JSON)
     */
    public function updatePicture(Request $request)
    {
        $path = 'images/imag_user/';
        $file = $request->file('admin_image');
        $new_name = 'UIMG_' . date('Ymd') . uniqid() . '.jpg';
        
        // Upload รูปภาพใหม่
        $upload = $file->move(public_path($path), $new_name);
     
        if (!$upload) {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong, upload new picture failed.']);
        } else {
            // Get old picture
            $oldPicture = User::find(Auth::user()->id)->getAttributes()['picture'];

            if ($oldPicture != '') {
                if (\File::exists(public_path($path . $oldPicture))) {
                    \File::delete(public_path($path . $oldPicture));
                }
            }

            // Update DB
            $update = User::find(Auth::user()->id)->update(['picture' => $new_name]);

            if (!$update) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong, updating picture in db failed.']);
            } else {
                return response()->json(['status' => 1, 'msg' => 'Your profile picture has been updated successfully']);
            }
        }
    }
}
