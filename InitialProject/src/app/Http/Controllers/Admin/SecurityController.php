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

class SecurityController extends Controller
{
    public function getSecurityStats()
    {
        $now = Carbon::now();
        $dayAgo = $now->copy()->subDay();

        return [
            'failed_logins' => SecurityEvent::where('event_type', 'failed_login')
                ->where('created_at', '>=', $dayAgo)
                ->count(),
            'suspicious_ips' => SecurityEvent::where('threat_level', 'high')
                ->where('created_at', '>=', $dayAgo)
                ->distinct('ip_address')
                ->count(),
            'blocked_attempts' => Cache::get('blocked_ips_count', 0),
            'total_monitoring' => SecurityEvent::where('created_at', '>=', $dayAgo)->count(),
        ];
    }

    public function blockIP(Request $request)
    {
        $ip = $request->input('ip');
        
        if (!$ip) {
            return response()->json(['success' => false, 'message' => 'IP address is required'], 400);
        }

        // Get current blocked IPs from cache or empty array if none
        $blockedIPs = Cache::get('blocked_ips', []);
        
        // Add new IP if not already blocked
        if (!in_array($ip, $blockedIPs)) {
            $blockedIPs[] = $ip;
            Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
            Cache::increment('blocked_ips_count');

            // Log the blocking event
            SecurityEvent::create([
                'event_type' => 'ip_blocked',
                'icon_class' => 'mdi-shield-lock',
                'ip_address' => $ip,
                'details' => 'IP address blocked by administrator',
                'threat_level' => 'high',
                'user_agent' => $request->userAgent(),
                'location' => null, // You might want to implement IP geolocation here
                'request_details' => [
                    'blocked_by' => auth()->id(),
                    'reason' => 'Manual block by administrator'
                ]
            ]);

            return response()->json(['success' => true, 'message' => 'IP blocked successfully']);
        }

        return response()->json(['success' => false, 'message' => 'IP is already blocked'], 400);
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
        $query = SecurityEvent::with('user')->orderBy('created_at', 'desc');

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
        $eventTypes = SecurityEvent::distinct('event_type')->pluck('event_type');

        // Paginate results
        $events = $query->paginate(20)->withQueryString();

        return view('admin.security.events', compact('events', 'eventTypes'));
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
