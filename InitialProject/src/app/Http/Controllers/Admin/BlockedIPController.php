<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SecurityEvent;

class BlockedIPController extends Controller
{
    public function index()
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        $ipDetails = [];

        foreach ($blockedIPs as $ip) {
            $blockInfo = Cache::get("block_info:{$ip}", []);
            $threatScore = Cache::get("threat_score:{$ip}", 0);
            
            // Get the last 5 security events for this IP
            $recentEvents = SecurityEvent::where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $ipDetails[] = [
                'ip' => $ip,
                'blocked_at' => $blockInfo['blocked_at'] ?? 'Unknown',
                'reason' => $blockInfo['reason'] ?? 'N/A',
                'threat_score' => $threatScore,
                'trigger_event' => $blockInfo['trigger_event'] ?? 'N/A',
                'blocked_by' => $blockInfo['blocked_by'] ?? 'system',
                'recent_events' => $recentEvents
            ];
        }

        return view('admin.security.blocked-ips', compact('ipDetails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        $ip = $request->input('ip');
        $blockedIPs = Cache::get('blocked_ips', []);

        if (in_array($ip, $blockedIPs)) {
            return response()->json([
                'success' => false,
                'message' => 'IP is already blocked'
            ], 400);
        }

        $blockedIPs[] = $ip;
        Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
        Cache::increment('blocked_ips_count');

        // Store block information
        Cache::put("block_info:{$ip}", [
            'blocked_at' => now()->format('Y-m-d H:i:s'),
            'reason' => $request->input('reason', 'Manually blocked by administrator'),
            'threat_score' => 10,
            'trigger_event' => 'manual_block',
            'blocked_by' => auth()->id()
        ], now()->addDays(7));

        // Log the manual block
        SecurityEvent::create([
            'event_type' => 'ip_blocked',
            'icon_class' => 'mdi-shield-lock',
            'ip_address' => $ip,
            'details' => 'IP manually blocked by administrator',
            'threat_level' => 'high',
            'user_agent' => $request->userAgent(),
            'request_details' => [
                'blocked_by' => auth()->id(),
                'reason' => $request->input('reason', 'Manual block')
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'IP blocked successfully'
        ]);
    }

    public function destroy($ip)
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        
        if (!in_array($ip, $blockedIPs)) {
            return response()->json([
                'success' => false,
                'message' => 'IP is not blocked'
            ], 400);
        }

        $blockedIPs = array_diff($blockedIPs, [$ip]);
        Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
        Cache::decrement('blocked_ips_count');
        Cache::forget("block_info:{$ip}");
        Cache::forget("threat_score:{$ip}");

        // Log the unblock
        SecurityEvent::create([
            'event_type' => 'ip_unblocked',
            'icon_class' => 'mdi-shield-off',
            'ip_address' => $ip,
            'details' => 'IP manually unblocked by administrator',
            'threat_level' => 'low',
            'user_agent' => request()->userAgent(),
            'request_details' => [
                'unblocked_by' => auth()->id()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'IP unblocked successfully'
        ]);
    }

    public function clear()
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        
        foreach ($blockedIPs as $ip) {
            Cache::forget("block_info:{$ip}");
            Cache::forget("threat_score:{$ip}");
        }

        Cache::put('blocked_ips', [], now()->addDays(7));
        Cache::put('blocked_ips_count', 0, now()->addDays(7));

        return response()->json([
            'success' => true,
            'message' => 'All blocked IPs have been cleared'
        ]);
    }

    public function unblock(Request $request)
    {
        $ip = $request->input('ip');
        $blockedIPs = Cache::get('blocked_ips', []);

        if (in_array($ip, $blockedIPs)) {
            $blockedIPs = array_diff($blockedIPs, [$ip]);
            Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
            Cache::decrement('blocked_ips_count');
            Cache::forget("block_info:{$ip}");
            Cache::forget("threat_score:{$ip}");

            // Log the unblock event
            SecurityEvent::create([
                'event_type' => 'ip_unblocked',
                'icon_class' => 'mdi-shield-off',
                'ip_address' => $ip,
                'details' => "IP address unblocked by admin",
                'threat_level' => 'low',
                'user_agent' => request()->userAgent(),
                'request_details' => [
                    'unblocked_by' => auth()->user()->id,
                    'unblocked_at' => now()->toDateTimeString()
                ]
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'IP not found in blocked list']);
    }
} 