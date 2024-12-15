<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * สถานที่ที่จะเปลี่ยนเส้นทางผู้ใช้หลังจากการลงทะเบียนเสร็จสิ้น
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * สร้างอินสแตนซ์ของคอนโทรลเลอร์ใหม่
     *
     * @return void
     */
    public function __construct()
    {
        // กำหนด middleware เพื่อให้แน่ใจว่าผู้ใช้ไม่ได้เข้าสู่ระบบ
        $this->middleware('guest');
    }

    /**
     * รับตัวตรวจสอบสำหรับคำขอลงทะเบียนที่เข้ามา
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // ตรวจสอบข้อมูลที่ส่งมาให้ตรงตามเงื่อนไขที่กำหนด
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'favoriteColor'=>'required',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * ฟังก์ชันสำหรับการลงทะเบียนผู้ใช้ใหม่
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function register(Request $request){

        // ตรวจสอบข้อมูลที่ส่งมาให้ตรงตามเงื่อนไขที่กำหนด
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'favoriteColor'=>'required',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** สร้างอวาตาร์ */
        $path = 'images/imag_user/';
        $fontPath = public_path('fonts/Oliciy.ttf');
        $char = strtoupper($request->name[0]);
        $newAvatarName = rand(12,34353).time().'_avatar.png';
        $dest = $path.$newAvatarName;

        $createAvatar = makeAvatar($fontPath,$dest,$char);
        $picture = $createAvatar == true ? $newAvatarName : '';

        // สร้างผู้ใช้ใหม่และบันทึกข้อมูลลงในฐานข้อมูล
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = 2;
        $user->favoriteColor = $request->favoriteColor;
        $user->picture = $picture;
        $user->password = \Hash::make($request->password);

        // ตรวจสอบว่าการบันทึกข้อมูลสำเร็จหรือไม่
        if( $user->save() ){
            return redirect()->back()->with('success','You are now successfully registered');
        }else{
            return redirect()->back()->with('error','Failed to register');
        }
    }
}
