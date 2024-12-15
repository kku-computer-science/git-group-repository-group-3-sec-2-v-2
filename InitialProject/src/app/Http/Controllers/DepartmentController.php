<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DepartmentController extends Controller
{
    /**
     * แสดงรายการของทรัพยากร
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         // กำหนด middleware เพื่อให้เฉพาะผู้ที่มีสิทธิ์ที่กำหนดเท่านั้นที่สามารถเข้าถึงฟังก์ชันต่างๆ ได้
         $this->middleware('permission:departments-list|departments-create|departments-edit|departments-delete', ['only' => ['index','store']]);
         $this->middleware('permission:departments-create', ['only' => ['create','store']]);
         $this->middleware('permission:departments-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:departments-delete', ['only' => ['destroy']]);
         //Redirect::to('dashboard')->send();
    }

    public function index(Request $request)
    {
        // ดึงข้อมูลแผนกทั้งหมดและแบ่งหน้า
        $data = Department::latest()->paginate(5);

        return view('departments.index',compact('data'));
    }

    /**
     * แสดงฟอร์มสำหรับสร้างทรัพยากรใหม่
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * เก็บทรัพยากรใหม่ในฐานข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // ตรวจสอบความถูกต้องของข้อมูลที่ส่งมา
        $this->validate($request, [
            'department_name_th' => 'required',
            'department_name_th' => 'required',
        ]);
        $input = $request->except(['_token']);
    
        // สร้างข้อมูลแผนกใหม่
        Department::create($input);
    
        return redirect()->route('departments.index')
            ->with('success','สร้างแผนกสำเร็จ');
    }

    /**
     * แสดงทรัพยากรที่ระบุ
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        return view('departments.show',compact('department'));
    }

    /**
     * แสดงฟอร์มสำหรับแก้ไขทรัพยากรที่ระบุ
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $department=Department::find($department->id);
       
        return view('departments.edit',compact('department'));
    }

    /**
     * อัพเดตทรัพยากรที่ระบุในฐานข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        // อัพเดตข้อมูลแผนก
        $department->update($request->all());
        return redirect()->route('departments.index')
                        ->with('success','อัพเดตแผนกสำเร็จ');
    }

    /**
     * ลบทรัพยากรที่ระบุออกจากฐานข้อมูล
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        // ลบข้อมูลแผนก
        $department->delete();
        return redirect()->route('departments.index')
                        ->with('success','ลบแผนกสำเร็จ');
    }
}
