<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Models\User;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * สถานที่ที่จะเปลี่ยนเส้นทางผู้ใช้หลังจากการรีเซ็ตรหัสผ่านเสร็จสิ้น
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * ฟังก์ชันเพื่อกำหนดเส้นทางการเปลี่ยนเส้นทางหลังจากการรีเซ็ตรหัสผ่าน
     *
     * @return string
     */
    protected function redirectTo(){
        // ตรวจสอบบทบาทของผู้ใช้และเปลี่ยนเส้นทางตามบทบาท
        if( Auth()->user()->role == 1 ){
            return route('admin.dashboard');
        }
        elseif( Auth()->user()->role == 2 ){
            return route('user.dashboard');
        }
    }
}
