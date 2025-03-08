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
        // Log logout activity before actually logging out
        if (Auth::check()) {
            \App\Models\ActivityLog::log(
                Auth::id(),
                'Logout',
                'User logged out of the system'
            );
        }
        
        $request->session()->flush();
        $request->session()->regenerate();
        Auth::logout();
        return redirect('/login');
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
            ErrorLogService::logAuthError(
                'Too many login attempts. User has been locked out.',
                $request->input('username') ?? ''
            );
            
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
            // Log validation errors
            ErrorLogService::logValidationError(
                $validator->errors()->toArray(),
                'login'
            );
            
            return redirect('login')->withErrors($validator->errors())->withInput();
        }

        $fieldType = filter_var($request->username ?? '', FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (auth()->attempt(array($fieldType => $input['username'] ?? '', 'password' => $input['password'] ?? ''))) {
            //success
            
            // Log successful login in the same format as other activities
            if (auth()->check() && auth()->id()) {
                \App\Models\ActivityLog::log(
                    auth()->id(), 
                    'Login', 
                    'User logged in successfully'
                );
            }
            
            if (Auth::check() && Auth::user() && Auth::user()->hasRole('admin')) {
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('student')) { //นักศึกษา
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('staff')) { //อาจารย์
                return redirect()->route('dashboard');
            } elseif (Auth::check() && Auth::user() && Auth::user()->hasRole('teacher')) { //เจ้าหน้าที่
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        } else {
            //fail
            $this->incrementLoginAttempts($request);
            
            // Log failed login attempts
            ErrorLogService::logAuthError(
                'Login Failed: Incorrect username or password.',
                $request->input('username') ?? ''
            );
            
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
        }
    }
}
