<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * สถานที่ที่จะเปลี่ยนเส้นทางผู้ใช้เมื่อ URL ที่ตั้งใจไว้ล้มเหลว
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
    }
}
