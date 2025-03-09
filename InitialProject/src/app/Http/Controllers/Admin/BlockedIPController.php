<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\SecurityEvent;
use App\Models\User;

class BlockedIPController extends Controller
{
    /**
     * Ensure the blocked_ips table exists
     */
    private function ensureTableExists()
    {
        if (!Schema::hasTable('blocked_ips')) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2025_03_08_200000_create_blocked_ips_table.php',
                    '--force' => true
                ]);
                return true;
            } catch (\Exception $e) {
                \Log::error('Failed to create blocked_ips table: ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    public function index()
    {
        // Try to ensure the table exists
        $tableExists = $this->ensureTableExists();
        
        $ipDetails = [];
        
        // First get cache-based IPs for backward compatibility
        $cachedBlockedIPs = Cache::get('blocked_ips', []);
        
        // Then get the blocked IPs from the database if the table exists
        if ($tableExists) {
            $databaseBlockedIPs = DB::table('blocked_ips')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Process database records first
            foreach ($databaseBlockedIPs as $blockedIP) {
                $ip = $blockedIP->ip_address;
                
                // Get the last 5 security events for this IP
                $recentEvents = SecurityEvent::where('ip_address', $ip)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
                
                // Get blocked by user information
                $blockedBy = $blockedIP->user_id ?? 'system';
                $blockedByDisplay = $blockedBy;
                
                if (is_numeric($blockedBy)) {
                    // Look up the user name from the User model
                    $user = User::find($blockedBy);
                    if ($user) {
                        $blockedByDisplay = $user->fname_en && $user->lname_en ? 
                            "{$user->fname_en} {$user->lname_en}" : 
                            ($user->fname_th && $user->lname_th ? 
                                "{$user->fname_th} {$user->lname_th}" : 
                                "Admin #{$blockedBy}");
                    } else {
                        $blockedByDisplay = "User #{$blockedBy}";
                    }
                }
                
                $ipDetails[] = [
                    'ip' => $ip,
                    'blocked_at' => $blockedIP->created_at ?? 'Unknown',
                    'reason' => $blockedIP->reason ?? 'N/A',
                    'threat_score' => Cache::get("threat_score:{$ip}", 5),
                    'trigger_event' => 'database_record',
                    'blocked_by' => $blockedBy,
                    'blocked_by_display' => $blockedByDisplay,
                    'recent_events' => $recentEvents,
                    'source' => 'database'
                ];
            }
        }
        
        // Now add any cache-only IPs that aren't in the database yet
        // This handles the transition from cache-only to database storage
        foreach ($cachedBlockedIPs as $ip) {
            // Skip if this IP is already in our results (from the database)
            if (array_filter($ipDetails, function($detail) use ($ip) {
                return $detail['ip'] === $ip;
            })) {
                continue;
            }
            
            $blockInfo = Cache::get("block_info:{$ip}", []);
            $threatScore = Cache::get("threat_score:{$ip}", 0);
            
            // Get the last 5 security events for this IP
            $recentEvents = SecurityEvent::where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Convert blocked_by from user ID to user name if it's a numeric ID
            $blockedBy = $blockInfo['blocked_by'] ?? 'system';
            $blockedByDisplay = $blockedBy;
            
            if (is_numeric($blockedBy)) {
                // Look up the user name from the User model
                $user = User::find($blockedBy);
                if ($user) {
                    $blockedByDisplay = $user->fname_en && $user->lname_en ? 
                        "{$user->fname_en} {$user->lname_en}" : 
                        ($user->fname_th && $user->lname_th ? 
                            "{$user->fname_th} {$user->lname_th}" : 
                            "Admin #{$blockedBy}");
                } else {
                    $blockedByDisplay = "User #{$blockedBy}";
                }
            }

            $ipDetails[] = [
                'ip' => $ip,
                'blocked_at' => $blockInfo['blocked_at'] ?? 'Unknown',
                'reason' => $blockInfo['reason'] ?? 'N/A',
                'threat_score' => $threatScore,
                'trigger_event' => $blockInfo['trigger_event'] ?? 'N/A',
                'blocked_by' => $blockedBy,
                'blocked_by_display' => $blockedByDisplay,
                'recent_events' => $recentEvents,
                'source' => 'cache'
            ];
            
            // If the table exists, migrate this cache entry to the database
            if ($tableExists) {
                try {
                    // Check if IP already exists in database
                    $exists = DB::table('blocked_ips')
                        ->where('ip_address', $ip)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('blocked_ips')->insert([
                            'ip_address' => $ip,
                            'reason' => $blockInfo['reason'] ?? 'Migrated from cache',
                            'user_id' => is_numeric($blockedBy) ? $blockedBy : null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error migrating cached IP to database: ' . $e->getMessage());
                }
            }
        }

        return view('admin.security.blocked-ips', compact('ipDetails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        $ip = $request->input('ip');
        
        // Ensure table exists
        $tableExists = $this->ensureTableExists();
        
        // Get threat score from request or default to 10
        $threatScore = $request->input('threat_score', 10);
        $reason = $request->input('reason', 'Manually blocked by administrator');
        
        try {
            // Store in database if table exists
            if ($tableExists) {
                // Check if IP already exists
                $exists = DB::table('blocked_ips')
                    ->where('ip_address', $ip)
                    ->exists();
                
                if (!$exists) {
                    DB::table('blocked_ips')->insert([
                        'ip_address' => $ip,
                        'reason' => $reason,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'IP is already blocked'
                    ], 400);
                }
            }
            
            // Also update cache for performance and backward compatibility
            $blockedIPs = Cache::get('blocked_ips', []);
            
            if (!in_array($ip, $blockedIPs)) {
                $blockedIPs[] = $ip;
                Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
                Cache::increment('blocked_ips_count');
                
                // Store block information
                Cache::put("block_info:{$ip}", [
                    'blocked_at' => now()->format('Y-m-d H:i:s'),
                    'reason' => $reason,
                    'threat_score' => $threatScore,
                    'trigger_event' => 'manual_block',
                    'blocked_by' => auth()->id()
                ], now()->addDays(7));
                
                // Store threat score
                Cache::put("threat_score:{$ip}", $threatScore, now()->addDays(7));
            }
        
            // Log the manual block
            SecurityEvent::create([
                'event_type' => 'ip_blocked',
                'icon_class' => 'mdi-shield-lock',
                'ip_address' => $ip,
                'details' => 'IP manually blocked by administrator',
                'threat_level' => $threatScore >= 8 ? 'high' : ($threatScore >= 5 ? 'medium' : 'low'),
                'user_agent' => $request->userAgent(),
                'request_details' => [
                    'blocked_by' => auth()->id(),
                    'reason' => $reason,
                    'threat_score' => $threatScore
                ]
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'IP blocked successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error blocking IP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error blocking IP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($ip)
    {
        try {
            // Remove from database if table exists
            if (Schema::hasTable('blocked_ips')) {
                DB::table('blocked_ips')
                    ->where('ip_address', $ip)
                    ->delete();
            }
            
            // Also remove from cache for backward compatibility
            $blockedIPs = Cache::get('blocked_ips', []);
            
            if (in_array($ip, $blockedIPs)) {
                $blockedIPs = array_diff($blockedIPs, [$ip]);
                Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
                Cache::decrement('blocked_ips_count');
                Cache::forget("block_info:{$ip}");
                Cache::forget("threat_score:{$ip}");
            }
        
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
        } catch (\Exception $e) {
            \Log::error('Error unblocking IP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error unblocking IP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clear()
    {
        try {
            // Get all blocked IPs from the database and cache
            $databaseIPs = [];
            if (Schema::hasTable('blocked_ips')) {
                $databaseIPs = DB::table('blocked_ips')
                    ->pluck('ip_address')
                    ->toArray();
            }
            
            $cachedIPs = Cache::get('blocked_ips', []);
            $allIPs = array_unique(array_merge($databaseIPs, $cachedIPs));
            
            // Clear the database table
            if (Schema::hasTable('blocked_ips')) {
                DB::table('blocked_ips')->truncate();
            }
            
            // Clear all cache entries
            foreach ($allIPs as $ip) {
                Cache::forget("block_info:{$ip}");
                Cache::forget("threat_score:{$ip}");
                
                // Log the unblock event for each IP
                SecurityEvent::create([
                    'event_type' => 'ip_unblocked',
                    'icon_class' => 'mdi-shield-off',
                    'ip_address' => $ip,
                    'details' => 'IP address unblocked during mass clear operation',
                    'threat_level' => 'low',
                    'user_agent' => request()->userAgent(),
                    'request_details' => [
                        'unblocked_by' => auth()->id(),
                        'unblocked_at' => now()->toDateTimeString(),
                        'operation' => 'clear_all'
                    ]
                ]);
            }
            
            Cache::put('blocked_ips', [], now()->addDays(7));
            Cache::put('blocked_ips_count', 0, now()->addDays(7));
        
            return response()->json([
                'success' => true,
                'message' => 'All blocked IPs have been cleared',
                'count' => count($allIPs)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error clearing blocked IPs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error clearing IPs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unblock(Request $request)
    {
        $ip = $request->input('ip');
        
        try {
            // Remove from database if table exists
            if (Schema::hasTable('blocked_ips')) {
                DB::table('blocked_ips')
                    ->where('ip_address', $ip)
                    ->delete();
            }
            
            // Also remove from cache
            $blockedIPs = Cache::get('blocked_ips', []);
            $found = in_array($ip, $blockedIPs);
            
            if ($found) {
                $blockedIPs = array_diff($blockedIPs, [$ip]);
                Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
                Cache::decrement('blocked_ips_count');
                Cache::forget("block_info:{$ip}");
                Cache::forget("threat_score:{$ip}");
            }
        
            // Log the unblock event
            SecurityEvent::create([
                'event_type' => 'ip_unblocked',
                'icon_class' => 'mdi-shield-off',
                'ip_address' => $ip,
                'details' => "IP address unblocked by admin",
                'threat_level' => 'low',
                'user_agent' => request()->userAgent(),
                'request_details' => [
                    'unblocked_by' => auth()->id(),
                    'unblocked_at' => now()->toDateTimeString()
                ]
            ]);
        
            if ($found) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'IP not found in blocked list']);
            }
        } catch (\Exception $e) {
            \Log::error('Error unblocking IP: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error unblocking IP: ' . $e->getMessage()
            ], 500);
        }
    }
} 