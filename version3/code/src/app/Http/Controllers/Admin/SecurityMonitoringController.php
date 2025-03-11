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
            \Log::info('Security monitoring: fetching failed logins data for the last 24 hours');
            $labels = [];
            $values = [];
            $aggregatedData = [];
            
            // Get all failed login attempts from the last 24 hours and aggregate in PHP
            $failedLogins = DB::table('security_events')
                ->leftJoin('users', 'security_events.user_id', '=', 'users.id')
                ->where('security_events.event_type', 'failed_login')
                ->where('security_events.created_at', '>=', Carbon::now()->subDay()) // Last 24 hours only
                ->select(
                    'security_events.id',
                    'security_events.user_id',
                    'security_events.request_details',
                    'users.email',
                    'users.fname_en',
                    'users.lname_en'
                )
                ->get();
                
            \Log::info('Security monitoring: found ' . count($failedLogins) . ' failed login attempts in the last 24 hours');
            
            // Process all failed logins and aggregate by username/email
            foreach ($failedLogins as $login) {
                $userLabel = '';
                $requestDetails = json_decode($login->request_details, true);
                
                // For registered users
                if (!empty($login->user_id)) {
                    // Use the most specific identifier available
                    if (!empty($login->fname_en) && !empty($login->lname_en)) {
                        $userLabel = $login->fname_en . ' ' . $login->lname_en;
                    } elseif (!empty($login->email)) {
                        $userLabel = $login->email;
                    } elseif (!empty($requestDetails['username'])) {
                        $userLabel = $requestDetails['username'];
                    } else {
                        $userLabel = 'User #' . $login->user_id;
                    }
                } 
                // For anonymous users
                else {
                    if (!empty($requestDetails['username'])) {
                        $userLabel = 'Failed: ' . $requestDetails['username'];
                    } else {
                        $userLabel = 'Anonymous User';
                    }
                }
                
                // Add to aggregated data
                if (isset($aggregatedData[$userLabel])) {
                    $aggregatedData[$userLabel]++;
                } else {
                    $aggregatedData[$userLabel] = 1;
                }
            }
            
            // Sort by count (highest first) and limit to top 15
            arsort($aggregatedData);
            $aggregatedData = array_slice($aggregatedData, 0, 15, true);
            
            // Convert to chart format
            foreach ($aggregatedData as $label => $count) {
                // Truncate long labels
                $labels[] = strlen($label) > 20 ? substr($label, 0, 17) . '...' : $label;
                $values[] = $count;
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

    /**
     * Get security events grouped by type for the chart
     */
    public function getSecurityEventsByTypeData(Request $request)
    {
        // Skip activity logging for chart data endpoints
        if (class_exists(\App\Models\ActivityLog::class) && method_exists(\App\Models\ActivityLog::class, 'shouldLogRoute')) {
            \App\Models\ActivityLog::shouldLogRoute(false);
        }
        
        // ตั้งค่าเริ่มต้น - จะดึงข้อมูล 24 ชั่วโมงที่ผ่านมา หรือทั้งหมด
        $fetchAllData = $request->has('all') && $request->input('all') === '1';
        
        try {
            \Log::info('Security monitoring: fetching security events by type data', [
                'fetch_all' => $fetchAllData ? 'Yes' : 'No (last 24 hours only)'
            ]);
            
            // ดึงข้อมูลทั้งหมดเพื่อทดสอบว่ามีข้อมูลในตารางหรือไม่
            $allEvents = DB::table('security_events')->count();
            \Log::info('Total security_events in the table: ' . $allEvents);
            
            // ตรวจสอบเวลาล่าสุดและเก่าสุดในตารางเพื่อดู date range
            $latestEvent = DB::table('security_events')->latest('created_at')->first();
            $oldestEvent = DB::table('security_events')->oldest('created_at')->first();
            
            if ($latestEvent && $oldestEvent) {
                \Log::info('Security events date range:', [
                    'oldest' => $oldestEvent->created_at,
                    'latest' => $latestEvent->created_at
                ]);
            }
            
            // สร้าง query builder
            $query = DB::table('security_events');
            
            // ถ้าไม่ได้เรียกหาข้อมูลทั้งหมด ให้จำกัดเฉพาะ 24 ชั่วโมงที่ผ่านมา
            if (!$fetchAllData) {
                $query->where('created_at', '>=', Carbon::now()->subDay());
            }
            
            // ดึงข้อมูลตาม query ที่สร้าง
            $securityEvents = $query->select('event_type', 'threat_level', 'created_at')->get();
                
            // Debug: Log the raw query results
            \Log::info('Security monitoring: raw security events data', [
                'event_count' => count($securityEvents),
                'sample' => count($securityEvents) > 0 ? json_encode($securityEvents->take(3)) : 'no data',
                'time_filter' => $fetchAllData ? 'No filter (all data)' : Carbon::now()->subDay()->toDateTimeString(),
                'unique_event_types' => $securityEvents->pluck('event_type')->unique()->values()->toArray()
            ]);
            
            // ตรวจสอบว่ามีข้อมูลหรือไม่
            if (count($securityEvents) == 0) {
                \Log::warning('Security monitoring: no security events found in the database' . 
                    ($fetchAllData ? '' : ' for the last 24 hours'));
                
                // สร้างข้อมูลตัวอย่างที่คล้ายคลึงกับข้อมูลจริง
                return response()->json([
                    'success' => true,
                    'generated' => true,
                    'message' => 'No data found, using sample data',
                    'data' => [
                        'labels' => [
                            'SQL Injection', 
                            'Failed Login', 
                            'XSS Attack', 
                            'IP Auto Blocked',
                            'Successful Login',
                            'DDoS Attempt',
                            'Brute Force Attempt',
                            'Logout'
                        ],
                        'values' => [4, 7, 3, 2, 1, 1, 1, 1],
                        'colors' => [
                            '#dc3545', // สีแดง - SQL Injection (high)
                            '#fd7e14', // สีส้ม - Failed Login (medium)
                            '#dc3545', // สีแดง - XSS Attack (high)
                            '#dc3545', // สีแดง - IP Blocked (high)
                            '#28a745', // สีเขียว - Successful Login (low)
                            '#dc3545', // สีแดง - DDoS (high) 
                            '#fd7e14', // สีส้ม - Brute Force (medium)
                            '#28a745'  // สีเขียว - Logout (low)
                        ]
                    ]
                ]);
            }
                
            // สร้างข้อมูลที่ aggregate ด้วย PHP
            $eventCounts = [];
            $eventThreatLevels = [];
            
            foreach ($securityEvents as $event) {
                $eventType = $event->event_type;
                
                if (!isset($eventCounts[$eventType])) {
                    $eventCounts[$eventType] = 0;
                    $eventThreatLevels[$eventType] = $event->threat_level;
                }
                
                $eventCounts[$eventType]++;
                
                // ถ้าพบ threat_level ที่สูงกว่า ให้ใช้ค่านั้นแทน
                if ($event->threat_level === 'high' || 
                   ($event->threat_level === 'medium' && $eventThreatLevels[$eventType] === 'low')) {
                    $eventThreatLevels[$eventType] = $event->threat_level;
                }
            }
            
            // Format data for the chart
            $labels = [];
            $values = [];
            $colors = [];
            
            // Define color scheme based on threat level
            $threatColors = [
                'high' => '#dc3545',    // Red
                'medium' => '#fd7e14',  // Orange
                'low' => '#28a745'      // Green
            ];
            
            // Map event types to user-friendly names
            $eventTypeNames = [
                'failed_login' => 'Failed Login',
                'successful_login' => 'Login Success',
                'logout' => 'Logout',
                'sql_injection_attempt' => 'SQL Injection',
                'xss_attempt' => 'XSS Attack',
                'ddos_attempt' => 'DDoS Attack',
                'brute_force_attempt' => 'Brute Force',
                'ip_auto_blocked' => 'IP Blocked'
            ];
            
            // เรียงข้อมูลตามจำนวนมากไปน้อย
            arsort($eventCounts);
            
            foreach ($eventCounts as $eventType => $count) {
                // Get user-friendly name or format the original
                $eventName = $eventTypeNames[$eventType] ?? ucwords(str_replace('_', ' ', $eventType));
                
                $labels[] = $eventName;
                $values[] = (int) $count;
                $colors[] = $threatColors[$eventThreatLevels[$eventType]] ?? '#6c757d'; // Default: gray
            }
            
            // Debug: Log the formatted data being sent to the chart
            \Log::info('Security monitoring: sending formatted chart data', [
                'labels' => $labels,
                'values' => $values,
                'colors_count' => count($colors)
            ]);
            
            // If no data found, provide sample data
            if (empty($labels)) {
                \Log::info('Security monitoring: no security events data found, using sample data');
                $labels = ['Sample SQL Injection', 'Sample XSS Attack', 'Sample Failed Login', 'Sample Brute Force', 'Sample IP Blocked'];
                $values = [5, 4, 3, 2, 1];
                $colors = ['#dc3545', '#fd7e14', '#28a745', '#dc3545', '#6c757d'];
            }
            
            $responseData = [
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values,
                    'colors' => $colors
                ]
            ];
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve security events data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return sample data in case of error
            return response()->json([
                'success' => false,
                'error' => 'Database error occurred: ' . $e->getMessage(),
                'data' => [
                    'labels' => ['SQL Injection', 'XSS Attack', 'Failed Login', 'Brute Force', 'IP Blocked'],
                    'values' => [5, 4, 3, 2, 1],
                    'colors' => ['#dc3545', '#fd7e14', '#28a745', '#dc3545', '#6c757d']
                ]
            ]);
        }
    }
} 