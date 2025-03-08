<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\ErrorLogService;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SecurityEvent;

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
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 10; // Default is 5
    protected $decayMinutes = 5; // Default is 1  //define 5 minute

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {

        $this->middleware(['guest'])->except('logout');
    }

    public function username()
    {
        return 'email';
    }

    public function logout(Request $request)
    {
        // Log logout event before actually logging out
        if (auth()->check()) {
            SecurityEvent::create([
                'event_type' => 'logout',
                'icon_class' => 'mdi-logout',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'details' => 'User logged out',
                'threat_level' => 'low',
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'logout_time' => now()->toDateTimeString()
                ]
            ]);
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectTo()
    {
        if (Auth::check() && Auth::user() && Auth::user()->hasRole('admin')) {
            return route('dashboard');
        } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('staff')) {
            return route('dashboard');
        } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('teacher')) {
            return route('dashboard');
        } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('student')) {
            return route('dashboard');
            //return view('home');
        }
        
        // Default fallback
        return route('dashboard');
    }

    public function login(Request $request)
    {
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            
            // Log too many login attempts
            SecurityEvent::create([
                'event_type' => 'account_locked',
                'icon_class' => 'mdi-lock-alert',
                'ip_address' => $request->ip(),
                'details' => 'Account temporarily locked due to too many login attempts',
                'threat_level' => 'high',
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'username' => $request->username,
                    'attempts' => $this->limiter()->attempts($this->throttleKey($request)),
                    'lockout_time' => now()->toDateTimeString()
                ]
            ]);
            
            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('username', 'password');

        $data = [
            "username" => $credentials['username'] ?? '',
            "password" => $credentials['password'] ?? ''
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        $input = $request->all();
        
        if ($validator->fails()) {
            // Log validation errors as security event
            SecurityEvent::create([
                'event_type' => 'invalid_login_attempt',
                'icon_class' => 'mdi-alert',
                'ip_address' => $request->ip(),
                'details' => 'Login attempt with invalid input',
                'threat_level' => 'low',
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'errors' => $validator->errors()->toArray(),
                    'attempt_time' => now()->toDateTimeString()
                ]
            ]);
            
            return redirect('login')->withErrors($validator->errors())->withInput();
        }

        $fieldType = filter_var($request->username ?? '', FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (auth()->attempt(array($fieldType => $input['username'] ?? '', 'password' => $input['password'] ?? ''))) {
            // Log successful login
            SecurityEvent::create([
                'event_type' => 'successful_login',
                'icon_class' => 'mdi-check-circle',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'details' => 'Successful login',
                'threat_level' => 'low',
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'login_time' => now()->toDateTimeString(),
                    'username' => $input['username'],
                    'login_type' => $fieldType
                ]
            ]);
            
            if (Auth::check() && Auth::user() && Auth::user()->hasRole('admin')) {
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('student')) {
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('staff')) {
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('teacher')) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        } else {
            $this->incrementLoginAttempts($request);
            
            // Log failed login attempt with threat level assessment
            $failedAttempts = $this->limiter()->attempts($this->throttleKey($request));
            $threatLevel = 'low';
            if ($failedAttempts > 8) {
                $threatLevel = 'high';
            } elseif ($failedAttempts > 4) {
                $threatLevel = 'medium';
            }

            SecurityEvent::create([
                'event_type' => 'failed_login',
                'icon_class' => 'mdi-alert-circle',
                'ip_address' => $request->ip(),
                'details' => 'Failed login attempt - Invalid credentials',
                'threat_level' => $threatLevel,
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'username' => $input['username'],
                    'login_type' => $fieldType,
                    'attempt_number' => $failedAttempts,
                    'attempt_time' => now()->toDateTimeString()
                ]
            ]);
            
            return redirect()->back()
                ->withInput($request->except('password'))
                ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
        }
    }

    /**
     * Override the failed login attempt method
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Log failed login attempt
        SecurityEvent::create([
            'event_type' => 'failed_login',
            'icon_class' => 'mdi-alert-circle',
            'ip_address' => $request->ip(),
            'details' => 'Failed login attempt for email: ' . $request->email,
            'threat_level' => $this->determineLoginThreatLevel($request->email),
            'user_agent' => $request->userAgent(),
            'request_details' => [
                'email' => $request->email,
                'attempt_time' => now()->toDateTimeString(),
            ]
        ]);

        return parent::sendFailedLoginResponse($request);
    }

    /**
     * Override the successful login method
     */
    protected function authenticated(Request $request, $user)
    {
        // Log successful login
        SecurityEvent::create([
            'event_type' => 'successful_login',
            'icon_class' => 'mdi-check-circle',
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'details' => 'Successful login',
            'threat_level' => 'low',
            'user_agent' => $request->userAgent(),
            'request_details' => [
                'login_time' => now()->toDateTimeString(),
                'email' => $user->email
            ]
        ]);

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Determine threat level based on failed attempts
     */
    private function determineLoginThreatLevel($email)
    {
        $recentFailedAttempts = SecurityEvent::where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subHours(1))
            ->where('request_details->email', $email)
            ->count();

        if ($recentFailedAttempts > 10) {
            return 'high';
        } elseif ($recentFailedAttempts > 5) {
            return 'medium';
        }
        return 'low';
    }
}
