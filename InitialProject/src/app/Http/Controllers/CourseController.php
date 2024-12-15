<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Degree;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * แสดงรายการของทรัพยากร
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::paginate(10);
        //return $programs;
		return view('courses.index',compact('courses'))->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * แสดงฟอร์มสำหรับสร้างทรัพยากรใหม่
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * เก็บทรัพยากรที่สร้างขึ้นใหม่ในที่เก็บข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $r = $request->validate([
            'course_code' => 'required',
            'course_name' => 'required',
        ]);

        $courseId = $request->course_id;
        $course = Course::find($courseId);
        $degree = Degree::find(2);
        //$course=Course::updateOrCreate(['id' => $courseId], ['course_code' => $request->course_code, 'course_name' => $request->course_name]);
        
        $degree->course()->updateOrCreate(['id' => $courseId], ['course_code' => $request->course_code, 'course_name' => $request->course_name]);
    
        
        if (empty($request->pro_id))
            $msg = 'Customer entry created successfully.';
        else
            $msg = 'Customer data is updated successfully';
        return redirect()->route('courses.index')->with('success', $msg);

    }

    /**
     * แสดงทรัพยากรที่ระบุ
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * แสดงฟอร์มสำหรับแก้ไขทรัพยากรที่ระบุ
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $where = array('id' => $id);
		$course = Course::where($where)->first();
		return response()->json($course);
    }

    /**
     * อัปเดตทรัพยากรที่ระบุในที่เก็บข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * ลบทรัพยากรที่ระบุออกจากที่เก็บข้อมูล
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Course::where('id', $id)->delete();
        return response()->json($course);
    }
}
