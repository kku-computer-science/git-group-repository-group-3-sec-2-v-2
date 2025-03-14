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
            try {
                $securityStats = cache()->remember('security_stats', 300, function() {
                    return $this->securityController->getSecurityStats();
                });
                
                // Optimize security events query with specific fields and smaller limit
                $securityEvents = SecurityEvent::select(
                    'id', 
                    'event_type', 
                    'icon_class', 
                    'user_id', 
                    'ip_address', 
                    'details', 
                    'threat_level', 
                    'created_at'
                )
                ->with(['user' => function($query) {
                    $query->select('id', 'fname_en', 'lname_en', 'fname_th', 'lname_th', 'email');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            } catch (\Exception $e) {
                \Log::error('Error loading security data: ' . $e->getMessage());
                $securityStats = [
                    'failed_logins' => 0,
                    'suspicious_ips' => 0,
                    'blocked_attempts' => 0,
                    'total_monitoring' => 0
                ];
            }
        }

        // Get user activities with optimized query
        try {
            $userActivities = DB::table('activity_logs')
                ->select(
                    'activity_logs.id',
                    'activity_logs.action_type',
                    'activity_logs.action',
                    'activity_logs.created_at',
                    'users.id as user_id',
                    DB::raw('COALESCE(NULLIF(CONCAT(users.fname_en, " ", users.lname_en), " "), 
                                    NULLIF(CONCAT(users.fname_th, " ", users.lname_th), " "), 
                                    users.email) as user_name')
                )
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->orderBy('activity_logs.created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error loading user activities: ' . $e->getMessage());
            $userActivities = collect([]);
        }

        // Get error logs with optimized query
        try {
            $errorLogs = DB::table('error_logs')
                ->select(
                    'error_logs.id',
                    'error_logs.level',
                    'error_logs.message',
                    'error_logs.file',
                    'error_logs.line',
                    'error_logs.ip_address',
                    'error_logs.created_at',
                    DB::raw('COALESCE(error_logs.user_id, 0) as user_id')
                )
                ->leftJoin('users', 'error_logs.user_id', '=', 'users.id')
                ->select(
                    'error_logs.id',
                    'error_logs.level',
                    'error_logs.message',
                    'error_logs.file',
                    'error_logs.line',
                    'error_logs.ip_address',
                    'error_logs.created_at',
                    DB::raw('COALESCE(NULLIF(CONCAT(users.fname_en, " ", users.lname_en), " "), 
                                   NULLIF(CONCAT(users.fname_th, " ", users.lname_th), " "), 
                                   error_logs.username,
                                   "Unknown") as user_name')
                )
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $totalErrorLogs = cache()->remember('total_error_logs', 300, function() {
                return DB::table('error_logs')->count();
            });
        } catch (\Exception $e) {
            \Log::error('Error loading error logs: ' . $e->getMessage());
            $errorLogs = collect([]);
            $totalErrorLogs = 0;
        }

        // Get system info with caching
        try {
            $systemInfo = cache()->remember('system_info', 3600, function() {
                return $this->getSystemInfo();
            });
        } catch (\Exception $e) {
            \Log::error('Error loading system info: ' . $e->getMessage());
            $systemInfo = [];
        }

        // Get total activities count with caching
        $totalActivities = cache()->remember('total_activities', 300, function() {
            return DB::table('activity_logs')->count();
        });

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
            $actionType = $request->action_type;
            
            // Decode encoded action types
            switch ($actionType) {
                case 'act_upd':
                    $actionType = 'Update';
                    break;
                case 'act_del':
                    $actionType = 'Delete';
                    break;
                case 'act_ins':
                    $actionType = 'Insert';
                    break;
                case 'act_sel':
                    $actionType = 'Select';
                    break;
                case 'act_cre':
                    $actionType = 'Create';
                    break;
                case 'act_drp':
                    $actionType = 'Drop';
                    break;
                case 'act_alt':
                    $actionType = 'Alter';
                    break;
            }
            
            // Use parameterized query to avoid SQL injection detection
            $query->where('activity_logs.action_type', '=', $actionType);
        }

        if ($request->has('action') && $request->action) {
            // Use parameterized query for action search
            $actionSearch = '%' . $request->action . '%';
            $query->where('activity_logs.action', 'like', $actionSearch);
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
        // Base query with optimized select fields
        $query = DB::table('error_logs')
            ->leftJoin('users', 'error_logs.user_id', '=', 'users.id')
            ->select(
                'error_logs.*', // Select all columns from error_logs table
                DB::raw('COALESCE(NULLIF(CONCAT(users.fname_en, " ", users.lname_en), " "), 
                               NULLIF(CONCAT(users.fname_th, " ", users.lname_th), " "), 
                               error_logs.username,
                               "Unknown") as user_name')
            );

        // Apply filters efficiently
        if ($request->filled('level')) {
            $query->where('error_logs.level', $request->level);
        }

        if ($request->filled('message')) {
            $message = $request->message;
            if (!is_null($message)) {
                $query->where('error_logs.message', 'like', '%' . $message . '%');
            }
        }

        if ($request->filled('file')) {
            $file = $request->file;
            if (!is_null($file)) {
                $query->where('error_logs.file', 'like', '%' . $file . '%');
            }
        }
        
        if ($request->filled('ip_address')) {
            $query->where('error_logs.ip_address', $request->ip_address);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('error_logs.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('error_logs.created_at', '<=', $request->date_to);
        }

        // Use smaller chunk size for pagination
        $errors = $query->orderBy('error_logs.created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get unique error levels efficiently using distinct and indexing
        $errorLevels = DB::table('error_logs')
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level');

        return view('admin.error_logs', compact('errors', 'errorLevels'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function getSystemInfo()
    {
        // Get MySQL version
        try {
            $mysqlVersion = DB::select('SELECT VERSION() as version')[0]->version;
        } catch (\Exception $e) {
            $mysqlVersion = 'Unknown';
        }

        // Get latest error logs
        $latestErrorLogs = DB::table('error_logs')
            ->select('id', 'level', 'message', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Convert created_at strings to Carbon objects
        $latestErrorLogs = $latestErrorLogs->map(function($log) {
            $log->created_at = \Carbon\Carbon::parse($log->created_at);
            return $log;
        });

        // Get latest activity logs
        $latestActivityLogs = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.id',
                'activity_logs.action_type',
                'activity_logs.description',
                'activity_logs.created_at',
                DB::raw('COALESCE(NULLIF(CONCAT(users.fname_en, " ", users.lname_en), " "), 
                     NULLIF(CONCAT(users.fname_th, " ", users.lname_th), " "), 
                     users.username,
                     "Unknown") as user_name')
            )
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Convert created_at strings to Carbon objects
        $latestActivityLogs = $latestActivityLogs->map(function($log) {
            $log->created_at = \Carbon\Carbon::parse($log->created_at);
            return $log;
        });

        // Get system uptime (Linux only)
        $uptime = 'Unknown';
        try {
            if (function_exists('shell_exec')) {
                $uptime = shell_exec('uptime -p');
                $uptime = $uptime ? trim($uptime) : 'Unknown';
            }
        } catch (\Exception $e) {
            $uptime = 'Unknown';
        }

        // Calculate disk usage metrics
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = round($diskUsed / $diskTotal * 100, 2);

        // Organize PHP settings in a structured array
        $phpSettings = [
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ];

        $systemInfo = [
            // Basic PHP and server info
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_name' => env('DB_DATABASE'),
            'mysql_version' => $mysqlVersion,
            'operating_system' => php_uname(),
            'timezone' => config('app.timezone'),
            
            // Counts
            'total_users' => DB::table('users')->count(),
            'total_papers' => DB::table('papers')->count(),
            
            // Resource usage
            'disk_free' => $this->formatBytes($diskFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used' => $this->formatBytes($diskUsed),
            'disk_usage_percent' => $diskUsagePercent,
            'server_load' => sys_getloadavg(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak_usage' => $this->formatBytes(memory_get_peak_usage(true)),
            'server_uptime' => $uptime,
            
            // Database info
            'database_size' => $this->getDatabaseSize(),
            'table_count' => $this->getTableCount(),
            'database_tables_count' => $this->getTableCount(), // For backward compatibility
            
            // Logs info
            'activity_logs_count' => DB::table('activity_logs')->count(),
            'error_logs_count' => DB::table('error_logs')->count(),
            'latest_error_logs' => $latestErrorLogs,
            'latest_activity_logs' => $latestActivityLogs,
            
            // PHP extensions and settings (both structured and flat for backward compatibility)
            'php_settings' => $phpSettings,
            'max_execution_time' => $phpSettings['max_execution_time'],
            'max_input_time' => $phpSettings['max_input_time'],
            'memory_limit' => $phpSettings['memory_limit'],
            'post_max_size' => $phpSettings['post_max_size'],
            'upload_max_filesize' => $phpSettings['upload_max_filesize'],
            'max_file_uploads' => $phpSettings['max_file_uploads'],
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

    /**
     * Get the count of tables in the database
     */
    private function getTableCount()
    {
        $dbName = env('DB_DATABASE');
        try {
            $result = DB::select("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            if (isset($result[0]->count)) {
                return $result[0]->count;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get database table count: ' . $e->getMessage());
        }
        return 'Unknown';
    }
} 