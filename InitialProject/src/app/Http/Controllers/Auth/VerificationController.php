<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * สถานที่ที่จะเปลี่ยนเส้นทางผู้ใช้หลังจากการยืนยันอีเมลเสร็จสิ้น
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
        // กำหนด middleware เพื่อให้แน่ใจว่าผู้ใช้ได้เข้าสู่ระบบแล้ว
        $this->middleware('auth');
        // กำหนด middleware เพื่อให้แน่ใจว่าลิงก์ยืนยันอีเมลถูกต้อง
        $this->middleware('signed')->only('verify');
        // กำหนด middleware เพื่อจำกัดจำนวนครั้งในการยืนยันอีเมลและการส่งอีเมลใหม่
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
