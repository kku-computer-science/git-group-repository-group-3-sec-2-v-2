<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use ThrottlesLogins;
    /**
     * สถานที่ที่จะเปลี่ยนเส้นทางผู้ใช้หลังจากการเข้าสู่ระบบเสร็จสิ้น
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 10; // จำนวนครั้งสูงสุดที่อนุญาตให้พยายามเข้าสู่ระบบ
    protected $decayMinutes = 5; // ระยะเวลาที่ต้องรอก่อนที่จะพยายามเข้าสู่ระบบใหม่

    /**
     * สร้างอินสแตนซ์ของคอนโทรลเลอร์ใหม่
     *
     * @return void
     */
    public function __construct()
    {
        // กำหนด middleware เพื่อให้แน่ใจว่าผู้ใช้ไม่ได้เข้าสู่ระบบ
        $this->middleware(['guest'])->except('logout');
    }

    /**
     * กำหนดฟิลด์ที่ใช้สำหรับการเข้าสู่ระบบ
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * ฟังก์ชันสำหรับการออกจากระบบ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // ล้างข้อมูลเซสชันและออกจากระบบ
        $request->session()->flush();
        $request->session()->regenerate();
        Auth::logout();
        return redirect('/login');
    }

    /**
     * ฟังก์ชันเพื่อกำหนดเส้นทางการเปลี่ยนเส้นทางหลังจากการเข้าสู่ระบบ
     *
     * @return string
     */
    protected function redirectTo()
    {
        // ตรวจสอบบทบาทของผู้ใช้และเปลี่ยนเส้นทางตามบทบาท
        if (Auth::user()->hasRole('admin')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('staff')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('teacher')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('student')) {
            return route('dashboard');
            //return view('home');
        }
    }

    /**
     * ฟังก์ชันสำหรับการเข้าสู่ระบบ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // ตรวจสอบว่ามีการพยายามเข้าสู่ระบบเกินจำนวนครั้งที่กำหนดหรือไม่
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // ตรวจสอบข้อมูลที่ส่งมาให้ตรงตามเงื่อนไขที่กำหนด
        $credentials = $request->only('username', 'password');
        $response = request('recaptcha');

        $data = [
            "username" => $credentials['username'],
            "password" => $credentials['password']
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        $input = $request->all();
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!$validator->fails()) {
            // ตรวจสอบข้อมูลการเข้าสู่ระบบและ reCAPTCHA
            if (auth()->attempt(array($fieldType => $input['username'], 'password' => $input['password'])) && $this->checkValidGoogleRecaptchaV3($response)) {
                // เปลี่ยนเส้นทางตามบทบาทของผู้ใช้
                if (Auth::user()->hasRole('admin')) {
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('student')) { //นักศึกษา
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('staff')) { //อาจารย์
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('teacher')) { //เจ้าหน้าที่
                    return redirect()->route('dashboard');
                } 
            } else {
                // เพิ่มจำนวนครั้งที่พยายามเข้าสู่ระบบและแสดงข้อผิดพลาด
                $this->incrementLoginAttempts($request);
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
            }
        } else {
            return redirect('login')->withErrors($validator->errors())->withInput();
        }
    }

    /**
     * ฟังก์ชันสำหรับตรวจสอบ reCAPTCHA ของ Google
     *
     * @param  string  $response
     * @return bool
     */
    public function checkValidGoogleRecaptchaV3($response)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $data = [
            'secret' => "6Ldpye4ZAAAAAKwmjpgup8vWWRwzL9Sgx8mE782u",
            'response' => $response
        ];

        $options = [
            'http' => [
                'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result);

        return $resultJson->success;
    }
}
