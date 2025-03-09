<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SecurityMonitoringController extends Controller
{
    /**
     * Get failed login attempts data for the chart
     */
    public function getFailedLoginsData()
    {
        // Skip activity logging for chart data endpoints
        if (class_exists(\App\Models\ActivityLog::class) && method_exists(\App\Models\ActivityLog::class, 'shouldLogRoute')) {
            \App\Models\ActivityLog::shouldLogRoute(false);
        }
        
        try {
            \Log::info('Security monitoring: fetching failed logins data');
            $labels = [];
            $values = [];
            
            // First try to get failed logins grouped by user
            $userFailedLogins = DB::table('activity_logs')
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->where('activity_logs.action_type', 'failed_login')
                ->where('activity_logs.created_at', '>=', Carbon::now()->subDay())
                ->select(
                    'users.email',
                    'users.fname_en',
                    'users.lname_en',
                    'activity_logs.description',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('users.id', 'users.email', 'users.fname_en', 'users.lname_en', 'activity_logs.description')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
                
            \Log::info('Security monitoring: found ' . count($userFailedLogins) . ' user failed logins');

            // Also get anonymous attempts (no user_id)
            $anonymousFailedLogins = DB::table('activity_logs')
                ->whereNull('user_id')
                ->where('action_type', 'failed_login')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->select(
                    'description',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('description')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
                
            \Log::info('Security monitoring: found ' . count($anonymousFailedLogins) . ' anonymous failed logins');
            
            // Process user-based failed logins
            foreach ($userFailedLogins as $login) {
                $userLabel = '';
                
                // Use the most specific identifier available
                if (!empty($login->fname_en) && !empty($login->lname_en)) {
                    $userLabel = $login->fname_en . ' ' . $login->lname_en;
                } elseif (!empty($login->email)) {
                    $userLabel = $login->email;
                } elseif (!empty($login->description)) {
                    // Try to extract email/username from description
                    preg_match('/email: ([^\s,]+)/', $login->description, $matches);
                    if (!empty($matches[1])) {
                        $userLabel = $matches[1];
                    } else {
                        $userLabel = 'User #' . substr(md5($login->description), 0, 6);
                    }
                } else {
                    $userLabel = 'Unknown User';
                }
                
                // Truncate long labels
                $labels[] = strlen($userLabel) > 20 ? substr($userLabel, 0, 17) . '...' : $userLabel;
                $values[] = (int) $login->count;
            }
            
            // Process anonymous login attempts
            foreach ($anonymousFailedLogins as $login) {
                $ipAddress = '';
                
                // Try to extract IP from description
                if (!empty($login->description)) {
                    preg_match('/IP: ([0-9\.]+)/', $login->description, $matches);
                    if (!empty($matches[1])) {
                        $ipAddress = $matches[1];
                    }
                }
                
                $label = !empty($ipAddress) ? 'Anonymous (' . $ipAddress . ')' : 'Anonymous User';
                $labels[] = strlen($label) > 20 ? substr($label, 0, 17) . '...' : $label;
                $values[] = (int) $login->count;
            }
            
            // If no data found, provide sample data
            if (empty($labels)) {
                \Log::info('Security monitoring: no failed login data found, using default');
                $labels = ['No failed login attempts'];
                $values = [0];
            }
            
            $responseData = [
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values
                ]
            ];
            
            \Log::info('Security monitoring: returning failed logins data', [
                'labels_count' => count($labels),
                'values_count' => count($values)
            ]);
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve login data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return sample data in case of error
            return response()->json([
                'success' => false,
                'error' => 'Database error occurred: ' . $e->getMessage(),
                'data' => [
                    'labels' => ['Sample User 1', 'Sample User 2', 'Sample User 3'],
                    'values' => [5, 3, 1]
                ]
            ]);
        }
    }

    /**
     * Get blocked requests data categorized by type
     */
    public function getBlockedRequestsData()
    {
        // Skip activity logging for chart data endpoints
        if (class_exists(\App\Models\ActivityLog::class) && method_exists(\App\Models\ActivityLog::class, 'shouldLogRoute')) {
            \App\Models\ActivityLog::shouldLogRoute(false);
        }
        
        try {
            // Get blocked requests by type
            $blockedRequests = DB::table('security_events')
                ->where('event_type', 'like', '%blocked%')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->select('event_type', DB::raw('COUNT(*) as count'))
                ->groupBy('event_type')
                ->get();

            $labels = [];
            $values = [];
            
            // Format data for the chart
            foreach ($blockedRequests as $request) {
                $labels[] = ucwords(str_replace(['blocked_', '_'], ['', ' '], $request->event_type));
                $values[] = $request->count;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve blocked requests data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Database error occurred: ' . $e->getMessage(),
                'data' => [
                    'labels' => ['SQL Injection', 'XSS', 'Brute Force', 'Rate Limit', 'Other'],
                    'values' => [30, 25, 20, 15, 10]
                ]
            ]);
        }
    }

    /**
     * Get system load data (CPU, Memory, Disk)
     */
    public function getSystemLoadData()
    {
        // Skip activity logging for chart data endpoints
        if (class_exists(\App\Models\ActivityLog::class) && method_exists(\App\Models\ActivityLog::class, 'shouldLogRoute')) {
            \App\Models\ActivityLog::shouldLogRoute(false);
        }
        
        try {
            // Get CPU usage
            $cpuUsage = $this->getCpuUsage();
            
            // Get memory usage
            $memoryUsage = $this->getMemoryUsage();
            
            // Get disk usage
            $diskUsage = $this->getDiskUsage();

            return response()->json([
                'success' => true,
                'data' => [
                    'cpu' => $cpuUsage,
                    'memory' => $memoryUsage,
                    'disk' => $diskUsage
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve system load data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving system load data: ' . $e->getMessage(),
                'data' => [
                    'cpu' => 45,
                    'memory' => 60,
                    'disk' => 75
                ]
            ]);
        }
    }

    /**
     * Get all dashboard data in one call
     */
    public function getDashboardData()
    {
        // Skip activity logging for chart data endpoints
        if (class_exists(\App\Models\ActivityLog::class) && method_exists(\App\Models\ActivityLog::class, 'shouldLogRoute')) {
            \App\Models\ActivityLog::shouldLogRoute(false);
        }
        
        try {
            // Get individual data components
            $failedLoginsResponse = $this->getFailedLoginsData();
            $blockedRequestsResponse = $this->getBlockedRequestsData();
            $systemLoadResponse = $this->getSystemLoadData();
            
            // Extract the data from the responses
            $failedLoginsData = $failedLoginsResponse->original['data'] ?? null;
            $blockedRequestsData = $blockedRequestsResponse->original['data'] ?? null;
            $systemLoadData = $systemLoadResponse->original['data'] ?? null;
            
            // Get security stats
            $securityStats = [
                'total_alerts' => $this->getTotalAlerts(),
                'blocked_ips' => count(Cache::get('blocked_ips', [])),
                'failed_logins' => $this->getRecentFailedLogins(),
                'suspicious_ips' => DB::table('security_events')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->where('threat_level', 'high')
                    ->distinct('ip_address')
                    ->count(),
                'blocked_attempts' => DB::table('security_events')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->where('event_type', 'like', '%blocked%')
                    ->count(),
                'total_monitoring' => DB::table('security_events')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->count()
            ];
            
            return response()->json([
                'success' => true,
                'failedLogins' => $failedLoginsData,
                'blockedRequests' => $blockedRequestsData,
                'systemLoad' => $systemLoadData,
                'securityStats' => $securityStats
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating dashboard data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error generating dashboard data: ' . $e->getMessage(),
                // Fallback data
                'failedLogins' => [
                    'labels' => ['Sample User 1', 'Sample User 2', 'Sample User 3'],
                    'values' => [5, 3, 1]
                ],
                'blockedRequests' => [
                    'labels' => ['SQL Injection', 'XSS', 'Brute Force', 'Rate Limit', 'Other'],
                    'values' => [30, 25, 20, 15, 10]
                ],
                'systemLoad' => [
                    'cpu' => 45,
                    'memory' => 60,
                    'disk' => 75
                ],
                'securityStats' => [
                    'total_alerts' => 0,
                    'blocked_ips' => 0,
                    'failed_logins' => 0,
                    'suspicious_ips' => 0,
                    'blocked_attempts' => 0,
                    'total_monitoring' => 0
                ]
            ]);
        }
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $load = sys_getloadavg();
            $cores = (int)shell_exec('nproc');
            return min(100, round(($load[0] / $cores) * 100));
        }
        
        // Fallback for non-Linux systems
        return random_int(20, 80); // Simulated value for development
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            $memory_usage = round($mem[2]/$mem[1]*100);
            return $memory_usage;
        }
        
        // Fallback for non-Linux systems
        return round(memory_get_usage(true) / memory_get_peak_usage(true) * 100);
    }

    /**
     * Get disk usage percentage
     */
    private function getDiskUsage()
    {
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        
        return round(($disk_total - $disk_free) / $disk_total * 100);
    }

    /**
     * Get total number of security alerts
     */
    private function getTotalAlerts()
    {
        return DB::table('security_events')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();
    }

    /**
     * Get number of failed logins in the last 24 hours
     */
    private function getRecentFailedLogins()
    {
        return DB::table('activity_logs')
            ->where('action_type', 'failed_login')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();
    }
} 