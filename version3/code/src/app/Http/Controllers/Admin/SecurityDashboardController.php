<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\SecurityMonitoringService;

class SecurityDashboardController extends Controller
{
    protected $securityService;

    public function __construct(SecurityMonitoringService $securityService)
    {
        $this->securityService = $securityService;
        $this->middleware('auth');
    }

    /**
     * Display the security monitoring dashboard
     */
    public function index()
    {
        try {
            // Get metrics data for all dashboard sections
            $securityOverview = $this->getSecurityOverview();
            $userActivity = $this->getUserActivityMetrics();
            $apiRequestMetrics = $this->getApiRequestMetrics();
            $threatDetectionLogs = $this->getThreatDetectionLogs();
            $performanceMetrics = $this->getPerformanceMetrics();
            
            return view('admin.security.dashboard', compact(
                'securityOverview',
                'userActivity',
                'apiRequestMetrics',
                'threatDetectionLogs',
                'performanceMetrics'
            ));
        } catch (\Exception $e) {
            \Log::error('Error loading security dashboard: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.errors.dashboard-error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ])->withErrors(['dashboard' => 'Failed to load the security dashboard. Please try again later.']);
        }
    }

    /**
     * Get Security Overview Metrics
     */
    private function getSecurityOverview()
    {
        // Cache for 5 minutes to optimize performance
        return Cache::remember('security_overview_metrics', 300, function() {
            try {
                $totalSecurityAlerts = SecurityEvent::count();
                
                $activeThreats = SecurityEvent::where('threat_level', 'high')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
                
                // Get blocked IPs count (either from cache or database)
                $blockedIPs = Cache::get('blocked_ips_count', function() {
                    if (Schema::hasTable('blocked_ips')) {
                        return DB::table('blocked_ips')->count();
                    }
                    return 0;
                });
                
                $suspiciousLoginAttempts = SecurityEvent::where('event_type', 'brute_force_attempt')
                    ->orWhere('event_type', 'failed_login')
                    ->count();
                
                // Get threat level distribution for chart
                $threatLevelDistribution = SecurityEvent::select('threat_level', DB::raw('count(*) as count'))
                    ->groupBy('threat_level')
                    ->orderBy('threat_level')
                    ->get()
                    ->pluck('count', 'threat_level')
                    ->toArray();
                
                // Ensure all threat levels are present for the chart
                $threatLevels = ['low', 'medium', 'high'];
                foreach ($threatLevels as $level) {
                    if (!isset($threatLevelDistribution[$level])) {
                        $threatLevelDistribution[$level] = 0;
                    }
                }
                
                // Get event types for chart
                $eventTypeDistribution = SecurityEvent::select('event_type', DB::raw('count(*) as count'))
                    ->groupBy('event_type')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->pluck('count', 'event_type')
                    ->toArray();
                
                // If no events, provide placeholder data
                if (empty($eventTypeDistribution)) {
                    $eventTypeDistribution = [
                        'failed_login' => 0,
                        'brute_force_attempt' => 0,
                        'sql_injection_attempt' => 0,
                        'xss_attempt' => 0,
                        'unauthorized_access' => 0
                    ];
                }
                
                return [
                    'total_security_alerts' => $totalSecurityAlerts,
                    'active_threats' => $activeThreats,
                    'blocked_ips' => $blockedIPs,
                    'suspicious_login_attempts' => $suspiciousLoginAttempts,
                    'threat_level_distribution' => $threatLevelDistribution,
                    'event_type_distribution' => $eventTypeDistribution
                ];
            } catch (\Exception $e) {
                \Log::error('Error fetching security overview metrics: ' . $e->getMessage());
                return [
                    'total_security_alerts' => 0,
                    'active_threats' => 0,
                    'blocked_ips' => 0,
                    'suspicious_login_attempts' => 0,
                    'threat_level_distribution' => ['low' => 0, 'medium' => 0, 'high' => 0],
                    'event_type_distribution' => [
                        'failed_login' => 0,
                        'brute_force_attempt' => 0,
                        'sql_injection_attempt' => 0,
                        'xss_attempt' => 0,
                        'unauthorized_access' => 0
                    ]
                ];
            }
        });
    }

    /**
     * Get User Activity & Authentication Monitoring Metrics
     */
    private function getUserActivityMetrics()
    {
        return Cache::remember('user_activity_metrics', 300, function() {
            try {
                $now = Carbon::now();
                $yesterday = $now->copy()->subHours(24);
                
                // Recent successful logins
                $successfulLogins = SecurityEvent::where('event_type', 'successful_login')
                    ->where('created_at', '>=', $yesterday)
                    ->count();
                
                // Failed login attempts (for brute force detection)
                $failedLogins = SecurityEvent::where('event_type', 'failed_login')
                    ->orWhere('event_type', 'brute_force_attempt')
                    ->where('created_at', '>=', $yesterday)
                    ->count();
                
                // Account lockouts
                $accountLockouts = SecurityEvent::where('event_type', 'account_lockout')
                    ->where('created_at', '>=', $yesterday)
                    ->count();
                
                // New user registrations
                $newRegistrations = User::where('created_at', '>=', $yesterday)->count();
                
                // Login activity timeline for chart (hourly for last 24 hours)
                $loginTimeline = [];
                for ($i = 23; $i >= 0; $i--) {
                    $hourStart = $now->copy()->subHours($i);
                    $hourEnd = $now->copy()->subHours($i-1);
                    
                    $successCount = SecurityEvent::where('event_type', 'successful_login')
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->count();
                    
                    $failCount = SecurityEvent::where('event_type', 'failed_login')
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->count();
                    
                    $loginTimeline[] = [
                        'hour' => $hourStart->format('H:i'),
                        'successful' => $successCount,
                        'failed' => $failCount
                    ];
                }
                
                return [
                    'successful_logins' => $successfulLogins,
                    'failed_logins' => $failedLogins,
                    'account_lockouts' => $accountLockouts,
                    'new_registrations' => $newRegistrations,
                    'login_timeline' => $loginTimeline
                ];
            } catch (\Exception $e) {
                \Log::error('Error fetching user activity metrics: ' . $e->getMessage());
                
                // Generate empty timeline data
                $loginTimeline = [];
                $now = Carbon::now();
                for ($i = 23; $i >= 0; $i--) {
                    $hourStart = $now->copy()->subHours($i);
                    $loginTimeline[] = [
                        'hour' => $hourStart->format('H:i'),
                        'successful' => 0,
                        'failed' => 0
                    ];
                }
                
                return [
                    'successful_logins' => 0,
                    'failed_logins' => 0,
                    'account_lockouts' => 0,
                    'new_registrations' => 0,
                    'login_timeline' => $loginTimeline
                ];
            }
        });
    }

    /**
     * Get API & Request Monitoring Metrics
     */
    private function getApiRequestMetrics()
    {
        return Cache::remember('api_request_metrics', 300, function() {
            try {
                // Top accessed endpoints
                $topEndpoints = SecurityEvent::select('request_details->url as url', DB::raw('count(*) as count'))
                    ->whereNotNull('request_details->url')
                    ->groupBy('url')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($item) {
                        // Extract endpoint from full URL
                        $url = json_decode($item->url);
                        $path = parse_url($url, PHP_URL_PATH) ?? $url;
                        return [
                            'endpoint' => $path,
                            'count' => $item->count
                        ];
                    });
                
                // If no data, provide placeholders
                if ($topEndpoints->isEmpty()) {
                    $topEndpoints = collect([
                        ['endpoint' => '/api/example1', 'count' => 0],
                        ['endpoint' => '/api/example2', 'count' => 0],
                        ['endpoint' => '/api/example3', 'count' => 0]
                    ]);
                }
                
                // Traffic spikes detection
                $trafficByHour = SecurityEvent::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', now()->subDay())
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->orderBy('hour')
                    ->get()
                    ->pluck('count', 'hour')
                    ->toArray();
                
                // Ensure all hours are present
                for ($i = 0; $i < 24; $i++) {
                    if (!isset($trafficByHour[$i])) {
                        $trafficByHour[$i] = 0;
                    }
                }
                ksort($trafficByHour);
                
                // Calculate average and detect spikes
                $avgTraffic = count($trafficByHour) > 0 ? array_sum($trafficByHour) / count($trafficByHour) : 0;
                $trafficSpikes = [];
                
                foreach ($trafficByHour as $hour => $count) {
                    if ($count > $avgTraffic * 1.5) {  // 50% above average is considered a spike
                        $trafficSpikes[$hour] = $count;
                    }
                }
                
                // Rate limit violations
                $rateLimitViolations = SecurityEvent::where('event_type', 'ddos_attempt')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
                
                // Blocked requests by WAF
                $blockedRequests = SecurityEvent::where(function($query) {
                    $query->where('event_type', 'sql_injection_attempt')
                        ->orWhere('event_type', 'xss_attempt');
                })
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
                
                return [
                    'top_endpoints' => $topEndpoints,
                    'traffic_by_hour' => $trafficByHour,
                    'traffic_spikes' => $trafficSpikes,
                    'rate_limit_violations' => $rateLimitViolations,
                    'blocked_requests' => $blockedRequests
                ];
            } catch (\Exception $e) {
                \Log::error('Error fetching API request metrics: ' . $e->getMessage());
                
                // Generate empty traffic by hour data
                $trafficByHour = [];
                for ($i = 0; $i < 24; $i++) {
                    $trafficByHour[$i] = 0;
                }
                
                return [
                    'top_endpoints' => collect([
                        ['endpoint' => '/api/example1', 'count' => 0],
                        ['endpoint' => '/api/example2', 'count' => 0],
                        ['endpoint' => '/api/example3', 'count' => 0]
                    ]),
                    'traffic_by_hour' => $trafficByHour,
                    'traffic_spikes' => [],
                    'rate_limit_violations' => 0,
                    'blocked_requests' => 0
                ];
            }
        });
    }

    /**
     * Get Incident & Threat Detection Logs
     */
    private function getThreatDetectionLogs()
    {
        try {
            // Fetch the latest security events (no caching needed as we want real-time data)
            $latestEvents = SecurityEvent::with('user:id,email')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
                
            // SQL Injection attempts
            $sqlInjectionAttempts = SecurityEvent::where('event_type', 'sql_injection_attempt')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
                
            // XSS attempts
            $xssAttempts = SecurityEvent::where('event_type', 'xss_attempt')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
                
            // Unauthorized access attempts
            $unauthorizedAccess = SecurityEvent::where('event_type', 'unauthorized_access')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
                
            // Server errors that might indicate attacks
            $serverErrors = SecurityEvent::where('event_type', 'server_error')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
                
            return [
                'latest_events' => $latestEvents,
                'sql_injection_attempts' => $sqlInjectionAttempts,
                'xss_attempts' => $xssAttempts,
                'unauthorized_access' => $unauthorizedAccess,
                'server_errors' => $serverErrors
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching threat detection logs: ' . $e->getMessage());
            
            return [
                'latest_events' => collect([]),
                'sql_injection_attempts' => 0,
                'xss_attempts' => 0,
                'unauthorized_access' => 0,
                'server_errors' => 0
            ];
        }
    }

    /**
     * Get Performance & Uptime Monitoring Metrics
     */
    private function getPerformanceMetrics()
    {
        return Cache::remember('performance_metrics', 300, function() {
            try {
                // Note: For real implementations, you would integrate with server monitoring tools
                // like New Relic, Scout APM, or custom monitoring solutions.
                // For this example, we'll use placeholder data or simple calculations
                
                // Sample system load (would come from server monitoring in real implementation)
                $systemLoad = [
                    'cpu' => random_int(10, 80), // Percentage
                    'memory' => random_int(20, 90), // Percentage
                    'disk' => random_int(40, 95) // Percentage
                ];
                
                // Average response time calculation (simplified)
                $avgResponseTime = SecurityEvent::select(DB::raw('AVG(TIMESTAMPDIFF(MICROSECOND, created_at, updated_at)/1000) as avg_response_time'))
                    ->whereDate('created_at', today())
                    ->value('avg_response_time') ?? 0;
                
                // Convert to milliseconds and round
                $avgResponseTime = round($avgResponseTime / 1000, 2);
                
                // Database query performance (placeholder)
                $databasePerformance = [
                    'avg_query_time' => random_int(5, 100) / 10, // milliseconds
                    'slow_queries' => random_int(0, 10),
                    'total_queries' => random_int(1000, 10000)
                ];
                
                // Uptime status (placeholder)
                $uptimeStatus = [
                    'status' => 'online',
                    'uptime_percentage' => random_int(990, 1000) / 10, // 99.0% to 100.0%
                    'last_downtime' => now()->subDays(random_int(1, 30))->format('Y-m-d H:i:s')
                ];
                
                return [
                    'system_load' => $systemLoad,
                    'avg_response_time' => $avgResponseTime,
                    'database_performance' => $databasePerformance,
                    'uptime_status' => $uptimeStatus
                ];
            } catch (\Exception $e) {
                \Log::error('Error fetching performance metrics: ' . $e->getMessage());
                
                return [
                    'system_load' => [
                        'cpu' => 50, 
                        'memory' => 50, 
                        'disk' => 50
                    ],
                    'avg_response_time' => 0,
                    'database_performance' => [
                        'avg_query_time' => 5,
                        'slow_queries' => 0,
                        'total_queries' => 1000
                    ],
                    'uptime_status' => [
                        'status' => 'online',
                        'uptime_percentage' => 100,
                        'last_downtime' => now()->subDays(30)->format('Y-m-d H:i:s')
                    ]
                ];
            }
        });
    }

    /**
     * API endpoint for real-time dashboard updates
     */
    public function getRealtimeData()
    {
        return response()->json([
            'security_overview' => $this->getSecurityOverview(),
            'user_activity' => $this->getUserActivityMetrics(),
            'api_requests' => $this->getApiRequestMetrics(),
            'threat_detection' => [
                'latest_events' => SecurityEvent::with('user:id,email')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
            ],
            'timestamp' => now()->toIso8601String()
        ]);
    }
} 