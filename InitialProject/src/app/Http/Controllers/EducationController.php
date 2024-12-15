<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EducationController extends Controller
{
    function updateEdInfo(Request $request)
    {
        // ตรวจสอบความถูกต้องของข้อมูลที่ส่งมา
        $validator = Validator::make($request->all(), [
            'b_uname' => 'required',
            'b_qua_name' => 'required',
            'b_year' => 'required',

            'm_uname' => 'required',
            'm_qua_name' => 'required',
            'm_year' => 'required',
            
            'd_uname' => 'required',
            'd_qua_name' => 'required',
            'd_year' => 'required',
        ]);

        //return response()->json(['status' => 1, 'msg' =>  $request->all()]);
        if (!$validator->passes()) {
            // ถ้าข้อมูลไม่ถูกต้อง ส่งกลับข้อผิดพลาด
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $id = Auth::user()->id;
            $user = User::find($id);

            // อัพเดตหรือสร้างข้อมูลการศึกษาระดับปริญญาตรี
            $user->education()->updateOrCreate([
                'level' => 1
            ],
            [
                'uname' => $request->b_uname,
                'qua_name' => $request->b_qua_name,
                'year' => $request->b_year,
                'level' => 1
            ]);

            // อัพเดตหรือสร้างข้อมูลการศึกษาระดับปริญญาโท
            $user->education()->updateOrCreate([
                'level'=> 2
            ],
            [
                'uname'=> $request->m_uname,
                'qua_name'=> $request->m_qua_name,
                'year' => $request->m_year,
                'level'=> 2
            ]);

            // อัพเดตหรือสร้างข้อมูลการศึกษาระดับปริญญาเอก
            $user->education()->updateOrCreate([
                'level'=> 3
            ],
            [
                'uname'=> $request->d_uname,
                'qua_name'=> $request->d_qua_name,
                'year' => $request->d_year,
                'level'=> 3
            ]);

            return response()->json(['status' => 1, 'msg' => 'อัพเดตข้อมูลโปรไฟล์สำเร็จ']);
        }
    }
}
