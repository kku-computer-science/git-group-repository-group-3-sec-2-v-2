<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SecurityEvent;
use App\Http\Controllers\Admin\SecurityController;

class AdminDashboardController extends Controller
{
    protected $securityController;

    public function __construct(SecurityController $securityController)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->securityController = $securityController;
    }

    public function index()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames();

        // Get security data if user is admin
        $securityStats = [];
        $securityEvents = collect([]);
        
        if ($user->hasRole('admin')) {
            $securityStats = $this->securityController->getSecurityStats();
            
            // Optimize the query to reduce memory usage:
            // 1. Limit fields to only what's needed
            // 2. Ensure we're only fetching recent records
            // 3. Use proper eager loading with specific fields
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
            ->take(10)
            ->get();

        // Get total count of user activities
        $totalActivities = DB::table('activity_logs')->count();

        // Get error logs (latest 10)
        $errorLogs = DB::table('error_logs')
            ->leftJoin('users', 'error_logs.user_id', '=', 'users.id')
            ->select(
                'error_logs.*',
                DB::raw('CASE 
                    WHEN users.id IS NOT NULL THEN CONCAT(
                        COALESCE(users.fname_en, users.fname_th), " ", 
                        COALESCE(users.lname_en, users.lname_th)
                    )
                    ELSE error_logs.username
                END as user_name')
            )
            ->orderBy('created_at', 'desc')
            ->take(10)
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

        return view('dashboard', compact(
            'user',
            'roles',
            'securityStats',
            'securityEvents',
            'userActivities',
            'errorLogs',
            'systemInfo',
            'totalActivities',
            'totalErrorLogs'
        ));
    }

    public function getUserActivities(Request $request)
    {
        $query = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 
                DB::raw("CASE 
                    WHEN users.fname_en IS NULL OR users.fname_en = '' THEN CONCAT(users.fname_th, ' ', users.lname_th)
                    ELSE CONCAT(users.fname_en, ' ', users.lname_en) 
                END as user_name"));

        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        if ($request->has('action_type') && $request->action_type) {
            $query->where('activity_logs.action_type', $request->action_type);
        }

        if ($request->has('action') && $request->action) {
            $query->where('activity_logs.action', 'like', '%' . $request->action . '%');
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('activity_logs.created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('activity_logs.created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('activity_logs.created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get all users for the filter dropdown
        $users = User::select('id', 
            DB::raw("CASE 
                WHEN fname_en IS NULL OR fname_en = '' THEN fname_th
                ELSE fname_en 
            END as fname"),
            DB::raw("CASE 
                WHEN lname_en IS NULL OR lname_en = '' THEN lname_th
                ELSE lname_en 
            END as lname"))
            ->orderBy('fname')
            ->get();
            
        // Get distinct action types for the filter dropdown
        $actionTypes = DB::table('activity_logs')
            ->select('action_type')
            ->whereNotNull('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type');

        return view('admin.activity_logs', compact('activities', 'users', 'actionTypes'));
    }

    public function getErrorLogs(Request $request)
    {
        $query = DB::table('error_logs')
            ->leftJoin('users', 'error_logs.user_id', '=', 'users.id')
            ->select(
                'error_logs.*',
                DB::raw('CASE 
                    WHEN users.id IS NOT NULL THEN CONCAT(
                        COALESCE(users.fname_en, users.fname_th), " ", 
                        COALESCE(users.lname_en, users.lname_th)
                    )
                    ELSE error_logs.username
                END as user_name')
            );

        // Apply filters
        if ($request->has('level') && $request->level) {
            $query->where('error_logs.level', $request->level);
        }

        if ($request->has('message') && $request->message) {
            $query->where('error_logs.message', 'like', '%' . $request->message . '%');
        }

        if ($request->has('file') && $request->file) {
            $query->where('error_logs.file', 'like', '%' . $request->file . '%');
        }
        
        if ($request->has('ip_address') && $request->ip_address) {
            $query->where('error_logs.ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('error_logs.created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('error_logs.created_at', '<=', $request->date_to);
        }

        $errors = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get unique error levels for filter dropdown
        $errorLevels = DB::table('error_logs')
            ->select('level')
            ->distinct()
            ->pluck('level');

        return view('admin.error_logs', compact('errors', 'errorLevels'));
    }

    public function getSystemInfo()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_name' => env('DB_DATABASE'),
            'total_users' => DB::table('users')->count(),
            'total_papers' => DB::table('papers')->count(),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            'server_load' => sys_getloadavg(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'database_size' => $this->getDatabaseSize(),
            'activity_logs_count' => DB::table('activity_logs')->count(),
            'error_logs_count' => DB::table('error_logs')->count(),
        ];

        return view('admin.system', compact('systemInfo'));
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    private function getDatabaseSize()
    {
        $dbName = env('DB_DATABASE');
        try {
            $result = DB::select("SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            if (isset($result[0]->size)) {
                return $this->formatBytes($result[0]->size);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get database size: ' . $e->getMessage());
        }
        return 'Unknown';
    }
} 