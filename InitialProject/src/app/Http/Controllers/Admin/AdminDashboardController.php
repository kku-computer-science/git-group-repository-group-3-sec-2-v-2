<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SecurityEvent;
use App\Models\BlockedIP;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get counts for dashboard summary - but only need the count, not full objects
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $pendingUsers = User::where('status', 'pending')->count();
        
        // Get recent login activity - select only needed fields
        $recentLogins = SecurityEvent::select('id', 'event_type', 'user_id', 'ip_address', 'created_at')
            ->where('event_type', 'login')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get security events for the security monitoring panel
        $recentSecurityEvents = SecurityEvent::select('id', 'event_type', 'icon_class', 'user_id', 'ip_address', 'details', 'threat_level', 'created_at')
            ->with(['user:id,fname_en,lname_en,fname_th,lname_th,email'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get blocked IPs
        $blockedIPs = BlockedIP::select('id', 'ip_address', 'reason', 'created_at', 'blocked_by')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get total statistics - use cache for these if possible to avoid repetitive count queries
        $totalLogins = SecurityEvent::where('event_type', 'login')->count();
        $failedLogins = SecurityEvent::where('event_type', 'failed_login')->count();
        $suspiciousActivities = SecurityEvent::where('threat_level', '>=', 3)->count();
        
        return view('dashboard', compact(
            'totalUsers', 
            'activeUsers', 
            'pendingUsers', 
            'recentLogins', 
            'recentSecurityEvents', 
            'blockedIPs',
            'totalLogins',
            'failedLogins',
            'suspiciousActivities'
        ));
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
} 