<?php

namespace App\Http\Controllers;

use App\Models\Educaton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\DB;
use App\Models\SecurityEvent;
use App\Http\Controllers\Admin\SecurityController;

class ProfileuserController extends Controller
{
    use LogsUserActions;

    protected $securityController;

    public function __construct(SecurityController $securityController)
    {
        $this->middleware('auth');
        $this->securityController = $securityController;
    }

    public function index()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames();

        // Initialize security data with empty values
        $securityStats = [
            'failed_logins' => 0,
            'suspicious_ips' => 0,
            'blocked_attempts' => 0,
            'total_monitoring' => 0
        ];
        $securityEvents = collect([]);

        // Get security data if user is admin
        if ($user->hasRole('admin')) {
            $securityStats = $this->securityController->getSecurityStats();
            
            // Optimize the query to reduce memory usage
            $securityEvents = SecurityEvent::select('id', 'event_type', 'icon_class', 'user_id', 'ip_address', 'details', 'threat_level', 'created_at')
                ->with(['user:id,fname_en,lname_en,fname_th,lname_th,email'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get user activities (latest 10)
        $userActivities = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 
                DB::raw("CASE 
                    WHEN users.fname_en IS NULL OR users.fname_en = '' THEN CONCAT(users.fname_th, ' ', users.lname_th)
                    ELSE CONCAT(users.fname_en, ' ', users.lname_en) 
                END as user_name"))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get total count of user activities
        $totalActivities = DB::table('activity_logs')->count();
        
        // Get error logs (last 10 entries)
        $errorLogs = DB::table('error_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get total count of error logs
        $totalErrorLogs = DB::table('error_logs')->count();

        // Get system information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_name' => env('DB_DATABASE'),
            'total_users' => DB::table('users')->count(),
            'total_papers' => DB::table('papers')->count(),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
        ];

        // ข้อมูลพื้นฐานสำหรับทุก role
        $data = [
            'user' => $user,
            'roles' => $roles,
            'securityStats' => $securityStats,
            'securityEvents' => $securityEvents,
            'userActivities' => $userActivities,
            'errorLogs' => $errorLogs,
            'totalActivities' => $totalActivities,
            'totalErrorLogs' => $totalErrorLogs,
            'systemInfo' => $systemInfo
        ];

        // ถ้าเป็น admin ให้เพิ่มข้อมูล dashboard
        if ($user->hasRole('admin')) {
            // Get user activities (paginated)
            $userActivities = DB::table('activity_logs')
                ->join('users', 'activity_logs.user_id', '=', 'users.id')
                ->select('activity_logs.*', 
                    DB::raw("CASE 
                        WHEN users.fname_en IS NULL OR users.fname_en = '' THEN CONCAT(users.fname_th, ' ', users.lname_th)
                        ELSE CONCAT(users.fname_en, ' ', users.lname_en) 
                    END as user_name"))
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            // Get error logs (last 10 entries)
            $errorLogs = DB::table('error_logs')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get system information
            $systemInfo = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_name' => env('DB_DATABASE'),
                'total_users' => DB::table('users')->count(),
                'total_papers' => DB::table('papers')->count(),
                'disk_free_space' => $this->formatBytes(disk_free_space('/')),
                'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            ];

            $data['userActivities'] = $userActivities;
            $data['errorLogs'] = $errorLogs;
            $data['systemInfo'] = $systemInfo;
        }

        // ถ้าเป็น researcher ให้เพิ่มข้อมูลที่เกี่ยวข้อง
        if ($user->hasRole('researcher')) {
            $data['papers'] = DB::table('papers')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $data['research_projects'] = DB::table('research_projects')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // ถ้าเป็น student ให้เพิ่มข้อมูลที่เกี่ยวข้อง
        if ($user->hasRole('student')) {
            $data['courses'] = DB::table('courses')
                ->join('user_courses', 'courses.id', '=', 'user_courses.course_id')
                ->where('user_courses.user_id', $user->id)
                ->select('courses.*')
                ->get();
        }

        return view('dashboard', $data);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    function profile()
    {
        return view('dashboards.users.profile');
    }
    function settings()
    {
        return view('dashboards.users.settings');
    }

    function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname_en' => 'required',
            'lname_en' => 'required',
            'fname_th' => 'required',
            'lname_th' => 'required',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id,
        ]);
    
        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        }
    
        $id = Auth::user()->id;
        $title_name_th = null;
    
        // Mapping title names
        switch ($request->title_name_en) {
            case "Mr.":
                $title_name_th = 'นาย';
                break;
            case "Miss":
                $title_name_th = 'นางสาว';
                break;
            case "Mrs.":
                $title_name_th = 'นาง';
                break;
        }
    
        $pos_en = null;
        $pos_th = null;
        $pos_eng = null;
        $pos_thai = null;
        $doctoral = null;
    
        // Role-based logic
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('student')) {
            $request->academic_ranks_en = null;
            $request->academic_ranks_th = null;
        } else {
            switch ($request->academic_ranks_en) {
                case "Professor":
                    $pos_en = 'Prof.';
                    $pos_th = 'ศ.';
                    break;
                case "Associate Professor":
                    $pos_en = 'Assoc. Prof.';
                    $pos_th = 'รศ.';
                    break;
                case "Assistant Professor":
                    $pos_en = 'Asst. Prof.';
                    $pos_th = 'ผศ.';
                    break;
                case "Lecturer":
                    $pos_en = 'Lecturer';
                    $pos_th = 'อ.';
                    break;
            }
    
            if ($request->has('pos')) {
                $pos_eng = $pos_en;
                $pos_thai = $pos_th;
            } else {
                if ($pos_en == "Lecturer") {
                    $pos_eng = $pos_en;
                    $pos_thai = $pos_th . 'ดร.';
                    $doctoral = 'Ph.D.';
                } else {
                    $pos_eng = $pos_en . ' Dr.';
                    $pos_thai = $pos_th . 'ดร.';
                    $doctoral = 'Ph.D.';
                }
            }
        }
    
        // Update user information
        $query = User::find($id)->update([
            'fname_en' => $request->fname_en,
            'lname_en' => $request->lname_en,
            'fname_th' => $request->fname_th,
            'lname_th' => $request->lname_th,
            'email' => $request->email,
            'academic_ranks_en' => $request->academic_ranks_en,
            'academic_ranks_th' => $request->academic_ranks_th,
            'position_en' => $pos_eng,
            'position_th' => $pos_thai,
            'title_name_en' => $request->title_name_en,
            'title_name_th' => $title_name_th,
            'doctoral_degree' => $doctoral,
        ]);
    
        if (!$query) {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong.']);
        }
        
        // Log profile update
        SecurityEvent::create([
            'event_type' => 'profile_updated',
            'icon_class' => 'mdi-account-edit',
            'user_id' => Auth::user()->id,
            'ip_address' => request()->ip(),
            'details' => 'Profile information updated',
            'threat_level' => 'low',
            'user_agent' => request()->userAgent(),
            'request_details' => [
                'updated_fields' => [
                    'email' => $request->email,
                    'name' => $request->fname_en . ' ' . $request->lname_en,
                    'academic_ranks' => $request->academic_ranks_en
                ],
                'update_time' => now()->toDateTimeString()
            ]
        ]);
    
        return response()->json(['status' => 1, 'msg' => 'success']);
    }
    

    function updatePicture(Request $request)
    {
        $path = 'images/imag_user/';
        //return 'aaaaaa';
        $file = $request->file('admin_image');
        $new_name = 'UIMG_' . date('Ymd') . uniqid() . '.jpg';

        //Upload new image
        $upload = $file->move(public_path($path), $new_name);

        if (!$upload) {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong, upload new picture failed.']);
        } else {
            //Get Old picture
            $oldPicture = User::find(Auth::user()->id)->getAttributes()['picture'];

            if ($oldPicture != '') {
                if (\File::exists(public_path($path . $oldPicture))) {
                    \File::delete(public_path($path . $oldPicture));
                }
            }

            //Update DB
            $update = User::find(Auth::user()->id)->update(['picture' => $new_name]);

            if (!$update) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong, updating picture in db failed.']);
            } else {
                // Log the profile picture update
                $this->logUpload('profile picture', Auth::user()->id, [
                    'filename' => $new_name,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getMimeType()
                ]);
                
                return response()->json(['status' => 1, 'msg' => 'Your profile picture has been updated successfully']);
            }
        }
    }


    function changePassword(Request $request)
    {
        //Validate form
        $validator = \Validator::make($request->all(), [
            'oldpassword' => [
                'required', function ($attribute, $value, $fail) {
                    if (!\Hash::check($value, Auth::user()->password)) {
                        // Log failed password change attempt
                        SecurityEvent::create([
                            'event_type' => 'failed_password_change',
                            'icon_class' => 'mdi-lock-alert',
                            'user_id' => Auth::user()->id,
                            'ip_address' => request()->ip(),
                            'details' => 'Failed password change attempt - incorrect current password',
                            'threat_level' => 'medium',
                            'user_agent' => request()->userAgent(),
                            'request_details' => [
                                'attempt_time' => now()->toDateTimeString()
                            ]
                        ]);
                        return $fail(__('The current password is incorrect'));
                    }
                },
                'min:8',
                'max:30'
            ],
            'newpassword' => 'required|min:8|max:30',
            'cnewpassword' => 'required|same:newpassword'
        ], [
            'oldpassword.required' => 'Enter your current password',
            'oldpassword.min' => 'Old password must have atleast 8 characters',
            'oldpassword.max' => 'Old password must not be greater than 30 characters',
            'newpassword.required' => 'Enter new password',
            'newpassword.min' => 'New password must have atleast 8 characters',
            'newpassword.max' => 'New password must not be greater than 30 characters',
            'cnewpassword.required' => 'ReEnter your new password',
            'cnewpassword.same' => 'New password and Confirm new password must match'
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {

            $update = User::find(Auth::user()->id)->update(['password' => \Hash::make($request->newpassword)]);

            if (!$update) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong, Failed to update password in db']);
            } else {
                // Log successful password change
                SecurityEvent::create([
                    'event_type' => 'password_changed',
                    'icon_class' => 'mdi-lock-check',
                    'user_id' => Auth::user()->id,
                    'ip_address' => request()->ip(),
                    'details' => 'Password changed successfully',
                    'threat_level' => 'low',
                    'user_agent' => request()->userAgent(),
                    'request_details' => [
                        'change_time' => now()->toDateTimeString()
                    ]
                ]);
                
                return response()->json(['status' => 1, 'msg' => 'Your password has been changed successfully']);
            }
        }
    }
}
