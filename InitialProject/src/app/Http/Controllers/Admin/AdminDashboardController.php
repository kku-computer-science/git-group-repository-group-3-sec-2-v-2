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
        // Get counts for dashboard summary
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $pendingUsers = User::where('status', 'pending')->count();
        
        // Get recent login activity 
        $recentLogins = SecurityEvent::where('event_type', 'login')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get security events for the security monitoring panel
        $recentSecurityEvents = SecurityEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Process user names for display
        foreach ($recentSecurityEvents as $event) {
            if ($event->user_id) {
                $event->username = $this->getUserDisplayName($event->user);
            }
        }
        
        // Get blocked IPs
        $blockedIPs = BlockedIP::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get total statistics
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