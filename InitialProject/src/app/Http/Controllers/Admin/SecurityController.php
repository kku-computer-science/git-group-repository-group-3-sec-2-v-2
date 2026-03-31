<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SecurityEventsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\SecurityMonitoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityController extends Controller
{
    protected $securityService;

    public function __construct(SecurityMonitoringService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function getSecurityStats()
    {
        try {
            return cache()->remember('security_stats', 300, function() {
                $now = now();
                $thirtyDaysAgo = $now->copy()->subDays(30);

                // Check if blocked_ips table exists
                $blockedAttempts = 0;
                if (Schema::hasTable('blocked_ips')) {
                    $blockedAttempts = DB::table('blocked_ips')
                        ->where('created_at', '>=', $thirtyDaysAgo)
                        ->count();
                }

                return [
                    'failed_logins' => DB::table('security_events')
                        ->where('event_type', 'failed_login')
                        ->where('created_at', '>=', $thirtyDaysAgo)
                        ->count(),

                    'suspicious_ips' => DB::table('security_events')
                        ->where('threat_level', '>=', 3)
                        ->where('created_at', '>=', $thirtyDaysAgo)
                        ->distinct('ip_address')
                        ->count(),

                    'blocked_attempts' => $blockedAttempts,

                    'total_monitoring' => DB::table('security_events')
                        ->where('created_at', '>=', $thirtyDaysAgo)
                        ->count()
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Error getting security stats: ' . $e->getMessage());
            return [
                'failed_logins' => 0,
                'suspicious_ips' => 0,
                'blocked_attempts' => 0,
                'total_monitoring' => 0
            ];
        }
    }

    public function blockIP(Request $request)
    {
        $ip = $request->input('ip');
        $reason = $request->input('reason', 'Manual block by administrator');
        
        if (!$ip) {
            return response()->json(['success' => false, 'message' => 'IP address is required'], 400);
        }

        try {
            // First check if blocked_ips table exists, if not create it
            if (!Schema::hasTable('blocked_ips')) {
                // Run the migration programmatically
                \Artisan::call('migrate', [
                    '--path' => 'database/migrations/2025_03_08_200000_create_blocked_ips_table.php',
                    '--force' => true
                ]);
            }

            // Check if IP already exists in the blocked_ips table
            $exists = DB::table('blocked_ips')
                ->where('ip_address', $ip)
                ->exists();

            if (!$exists) {
                // Add to database
                DB::table('blocked_ips')->insert([
                    'ip_address' => $ip,
                    'reason' => $reason,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Also maintain the cache for performance
                $blockedIPs = Cache::get('blocked_ips', []);
                if (!in_array($ip, $blockedIPs)) {
                    $blockedIPs[] = $ip;
                    Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
                    Cache::increment('blocked_ips_count');
                }

                // Log the blocking event using SecurityMonitoringService
                $this->securityService->logAttackAttempt(
                    $ip,
                    'ip_blocked',
                    'IP address blocked by administrator',
                    'high',
                    [
                        'blocked_by' => auth()->id(),
                        'reason' => $reason
                    ]
                );

                return response()->json(['success' => true, 'message' => 'IP address blocked successfully']);
            } else {
                return response()->json(['success' => true, 'message' => 'IP address is already blocked']);
            }
        } catch (\Exception $e) {
            \Log::error('Error blocking IP address: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to block IP: ' . $e->getMessage()], 500);
        }
    }

    public function logSecurityEvent($eventType, $details, $threatLevel = 'low', $userId = null)
    {
        $iconClasses = [
            'failed_login' => 'mdi-alert-circle',
            'suspicious_activity' => 'mdi-alert',
            'ip_blocked' => 'mdi-shield-lock',
            'unauthorized_access' => 'mdi-lock-alert',
            'default' => 'mdi-information'
        ];

        return SecurityEvent::create([
            'event_type' => $eventType,
            'icon_class' => $iconClasses[$eventType] ?? $iconClasses['default'],
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'details' => $details,
            'threat_level' => $threatLevel,
            'user_agent' => request()->userAgent(),
            'location' => null, // Implement IP geolocation if needed
            'request_details' => [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'headers' => request()->headers->all()
            ]
        ]);
    }

    public function events(Request $request)
    {
        // Optimize the base query to select only necessary fields
        $query = SecurityEvent::select('id', 'event_type', 'icon_class', 'user_id', 'ip_address', 'details', 'threat_level', 'request_details', 'created_at', 'additional_data')
            ->with(['user:id,fname_en,lname_en,fname_th,lname_th,email']);

        // Apply filters
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('threat_level')) {
            $query->where('threat_level', $request->threat_level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%");
            });
        }

        // Get distinct event types for filter dropdown
        $eventTypes = SecurityEvent::select('event_type')->distinct()->pluck('event_type');

        // Paginate results and add username to each event
        $events = $query->paginate(15)->through(function ($event) {
            $event->username = $this->getUserDisplayName($event->user);
            return $event;
        })->withQueryString();

        return view('admin.security.events', compact('events', 'eventTypes'));
    }

    /**
     * Helper method to get user display name
     */
    private function getUserDisplayName($user)
    {
        if (!$user) {
            return 'Unknown';
        }
        
        if ($user->fname_en && $user->lname_en) {
            return $user->fname_en . ' ' . $user->lname_en;
        } 
        
        if ($user->fname_th && $user->lname_th) {
            return $user->fname_th . ' ' . $user->lname_th;
        }
        
        if ($user->email) {
            return $user->email;
        }
        
        return 'Unknown';
    }

    public function export(Request $request)
    {
        $query = SecurityEvent::query();

        // Apply filters
        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('threat_level')) {
            $query->where('threat_level', $request->threat_level);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('event_type', 'like', "%{$search}%");
            });
        }

        $events = $query->latest()->get();

        $format = $request->get('format', 'csv');
        if (!in_array($format, ['csv', 'xlsx', 'pdf'])) {
            $format = 'csv';
        }

        $export = new SecurityEventsExport($events, $format);
        return $export->download();
    }
}
