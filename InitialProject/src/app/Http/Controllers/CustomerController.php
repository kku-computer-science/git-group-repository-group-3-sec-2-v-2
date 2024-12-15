<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Customer;
use Redirect,Response;
class CustomerController extends Controller
{

    /**
    * แสดงรายการของทรัพยากร
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        // ดึงข้อมูลลูกค้าทั้งหมดและแบ่งหน้า
        $customers = Customer::latest()->paginate(4);
        return view('customers.index',compact('customers'))->with('i', (request()->input('page', 1) - 1) * 4);
    }

    /**
    * แสดงฟอร์มสำหรับสร้างทรัพยากรใหม่
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('customers.create');
    }

    /**
    * เก็บทรัพยากรใหม่ในฐานข้อมูล
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        // ตรวจสอบความถูกต้องของข้อมูลที่ส่งมา
        $r=$request->validate([
        'name' => 'required',
        'email' => 'required',
        'address' => 'required',
        ]);
        $custId = $request->cust_id;
        // สร้างหรืออัพเดตข้อมูลลูกค้า
        Customer::updateOrCreate(['id' => $custId],['name' => $request->name, 'email' => $request->email,'address'=>$request->address]);
        if(empty($request->cust_id))
            $msg = 'สร้างข้อมูลลูกค้าสำเร็จ';
        else
            $msg = 'อัพเดตข้อมูลลูกค้าสำเร็จ';
        return redirect()->route('customers.index')->with('success',$msg);
    }

    /**
    * แสดงทรัพยากรที่ระบุ
    *
    * @param int $id
    * @return \Illuminate\Http\Response
    */
    public function show(Customer $customer)
    {
        return view('customers.show',compact('customer'));
    }

    /**
    * แสดงฟอร์มสำหรับแก้ไขทรัพยากรที่ระบุ
    *
    * @param int $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $where = array('id' => $id);
        $customer = Customer::where($where)->first();
        return response()->json($customer);
    }

    /**
    * อัพเดตทรัพยากรที่ระบุในฐานข้อมูล
    *
    * @param \Illuminate\Http\Request $request
    * @param int $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request)
    {
        //
    }

    /**
    * ลบทรัพยากรที่ระบุออกจากฐานข้อมูล
    *
    * @param int $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        // ลบข้อมูลลูกค้า
        $cust = Customer::where('id',$id)->delete();
        return response()->json($cust);
    }
}