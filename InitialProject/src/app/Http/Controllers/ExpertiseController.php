<?php

namespace App\Http\Controllers;

use App\Models\Expertise;
use App\Models\Fund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpertiseController extends Controller
{
    /**
     * แสดงรายการของทรัพยากร
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        if (auth()->user()->hasRole('admin')) {
            // ถ้าเป็น admin ให้ดึงข้อมูลทั้งหมด
            $experts = Expertise::all();
        } else {
            // ถ้าไม่ใช่ admin ให้ดึงข้อมูลเฉพาะที่เกี่ยวข้องกับผู้ใช้ที่ล็อกอินอยู่
            $experts = Expertise::with('user')->whereHas('user', function ($query) use ($id) {
                $query->where('users.id', '=', $id);
            })->paginate(10);
        }

        return view('expertise.index', compact('experts'));
    }

    /**
     * แสดงฟอร์มสำหรับสร้างทรัพยากรใหม่
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('expertise.create');
    }

    /**
     * เก็บทรัพยากรใหม่ในฐานข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $r = $request->validate([
            'expert_name' => 'required',
        ]);

        $exp = Expertise::find($request->exp_id);
        //return $exp;
        $exp_id = $request->exp_id;
        //dd($custId);
        if (auth()->user()->hasRole('admin')) {
            // ถ้าเป็น admin ให้ทำการอัพเดตข้อมูล
            $exp->update($request->all());
        } else {
            // ถ้าไม่ใช่ admin ให้ทำการสร้างหรืออัพเดตข้อมูลที่เกี่ยวข้องกับผู้ใช้ที่ล็อกอินอยู่
            $user = User::find(Auth::user()->id);
            $user->expertise()->updateOrCreate(['id' => $exp_id], ['expert_name' => $request->expert_name]);
        }

        if (empty($request->exp_id))
            $msg = 'สร้างข้อมูลความเชี่ยวชาญสำเร็จ';
        else
            $msg = 'อัพเดตข้อมูลความเชี่ยวชาญสำเร็จ';

        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('experts.index')->with('success', $msg);
        } else {
            //return response()->json(['status'=>1,'msg'=>'Your expertise info has been update successfuly.']);
            //return redirect()->back() ->with('alert', 'Updated!');
            return back()->withInput(['tab' => 'expertise']);
            //return response()->json(['status'=>1,'msg'=>'Your expertise info has been update successfuly.']);
        }

        //return redirect()->route('experts.index')->with('success',$msg);
    }

    /**
     * แสดงทรัพยากรที่ระบุ
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Expertise $expertise)
    {
        //return view('expertise.show',compact('expertise'));
        //$where = array('id' => $id);
        //$exp = Expertise::where($where)->first();
        return response()->json($expertise);
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
        $exp = Expertise::where($where)->first();
        return response()->json($exp);
    }

    /**
     * อัพเดตทรัพยากรที่ระบุในฐานข้อมูล
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
     * ลบทรัพยากรที่ระบุออกจากฐานข้อมูล
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //dd($id);
        $exp = Expertise::where('id', $id)->delete();
        $msg = 'ลบข้อมูลความเชี่ยวชาญสำเร็จ';
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('experts.index')->with('success', $msg);
        } else {
            //return response()->json(['status'=>1,'msg'=>'Your expertise info has been update successfuly.']);
            //return redirect()->back() ->with('alert', 'Updated!');
            return back()->withInput(['tab' => 'expertise']);
            //return response()->json(['status'=>1,'msg'=>'Your expertise info has been update successfuly.']);
        }
        //return response()->json($exp);
    }
}
