<?php

namespace App\Services;

use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityMonitoringService
{
    // Constants for thresholds
    const REQUEST_RATE_THRESHOLD = 100; // requests per minute
    const FAILED_LOGIN_THRESHOLD = 5;   // failed attempts per 5 minutes
    const CACHE_EXPIRY = 300;          // 5 minutes in seconds
    
    /**
     * Monitor request rate for potential DDoS attacks
     */
    public function monitorRequestRate($ip)
    {
        $key = "request_rate:{$ip}";
        $minute = now()->format('Y-m-d H:i');
        $requests = Cache::get($key, []);
        
        // Add current timestamp
        $requests[] = $minute;
        
        // Keep only requests from the last minute
        $requests = array_filter($requests, function($time) {
            return Carbon::parse($time)->diffInMinutes(now()) < 1;
        });
        
        Cache::put($key, $requests, now()->addMinutes(5));
        
        // Check if request rate exceeds threshold
        if (count($requests) > self::REQUEST_RATE_THRESHOLD) {
            $this->logAttackAttempt($ip, 'ddos_attempt', 'Abnormally high request rate detected', 'high');
            return true;
        }
        
        return false;
    }
    
    /**
     * Monitor failed login attempts
     */
    public function monitorFailedLogins($ip, $username = null)
    {
        $key = "failed_logins:{$ip}";
        $attempts = Cache::get($key, []);
        
        // Add current attempt
        $attempts[] = now()->timestamp;
        
        // Keep only attempts from the last 5 minutes
        $attempts = array_filter($attempts, function($timestamp) {
            return (now()->timestamp - $timestamp) < self::CACHE_EXPIRY;
        });
        
        Cache::put($key, $attempts, now()->addMinutes(5));
        
        // Check if failed attempts exceed threshold
        if (count($attempts) >= self::FAILED_LOGIN_THRESHOLD) {
            $this->logAttackAttempt(
                $ip,
                'brute_force_attempt',
                'Multiple failed login attempts detected' . ($username ? " for user: {$username}" : ''),
                'high'
            );
            return true;
        }
        
        return false;
    }
    
    /**
     * Monitor for SQL injection attempts
     */
    public function detectSQLInjection($input, $ip)
    {
        $sqlPatterns = [
            '/\b(union|select|insert|update|delete|drop|alter)\b/i',
            '/[\'";]/',
            '/--/',
            '/\b(or|and)\b.*?=.*?(\d+|\'|")/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logAttackAttempt(
                    $ip,
                    'sql_injection_attempt',
                    'Potential SQL injection pattern detected in request',
                    'high',
                    ['detected_pattern' => $pattern]
                );
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Monitor for XSS attempts
     */
    public function detectXSS($input, $ip)
    {
        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:[^\s]*/i',
            '/on\w+\s*=/i',
            '/<\s*img[^>]+src\s*=\s*[^>]+>/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logAttackAttempt(
                    $ip,
                    'xss_attempt',
                    'Potential XSS pattern detected in request',
                    'high',
                    ['detected_pattern' => $pattern]
                );
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Monitor for suspicious user behavior
     */
    public function detectSuspiciousBehavior($ip, $userAgent, $requestData)
    {
        $suspicious = false;
        $reasons = [];
        
        // Check for missing or suspicious user agent
        if (empty($userAgent) || strlen($userAgent) < 10) {
            $suspicious = true;
            $reasons[] = 'Suspicious user agent';
        }
        
        // Check for rapid page switching
        $key = "page_requests:{$ip}";
        $requests = Cache::get($key, []);
        $requests[] = now()->timestamp;
        $requests = array_filter($requests, fn($time) => (now()->timestamp - $time) < 60);
        Cache::put($key, $requests, now()->addMinutes(5));
        
        if (count($requests) > 30) { // More than 30 requests per minute
            $suspicious = true;
            $reasons[] = 'Rapid page switching';
        }
        
        // Check for automated tool signatures
        $automatedTools = ['curl', 'wget', 'python-requests', 'go-http-client'];
        foreach ($automatedTools as $tool) {
            if (stripos($userAgent, $tool) !== false) {
                $suspicious = true;
                $reasons[] = "Automated tool detected: {$tool}";
            }
        }
        
        if ($suspicious) {
            $this->logAttackAttempt(
                $ip,
                'suspicious_behavior',
                'Suspicious behavior detected: ' . implode(', ', $reasons),
                'medium',
                [
                    'reasons' => $reasons,
                    'user_agent' => $userAgent,
                    'request_data' => $requestData
                ]
            );
            return true;
        }
        
        return false;
    }
    
    /**
     * Log attack attempt
     */
    private function logAttackAttempt($ip, $type, $details, $threatLevel, $additionalData = [])
    {
        $iconClasses = [
            'ddos_attempt' => 'mdi-server-network-off',
            'brute_force_attempt' => 'mdi-lock-alert',
            'sql_injection_attempt' => 'mdi-database-alert',
            'xss_attempt' => 'mdi-script-text-alert',
            'suspicious_behavior' => 'mdi-account-alert',
        ];
        
        SecurityEvent::create([
            'event_type' => $type,
            'icon_class' => $iconClasses[$type] ?? 'mdi-alert',
            'ip_address' => $ip,
            'details' => $details,
            'threat_level' => $threatLevel,
            'user_agent' => request()->userAgent(),
            'location' => null,
            'request_details' => array_merge([
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'headers' => request()->headers->all()
            ], $additionalData)
        ]);
        
        // If threat level is high, consider automatic IP blocking
        if ($threatLevel === 'high') {
            $this->considerAutoBlock($ip, $type, $details);
        }
        
        Log::channel('security')->warning("Security event detected: {$type} from IP: {$ip}");
    }
    
    /**
     * Consider automatic IP blocking based on threat assessment
     */
    private function considerAutoBlock($ip, $type, $reason)
    {
        $key = "threat_score:{$ip}";
        $score = Cache::get($key, 0);
        
        // Increment threat score based on event type
        $scoreIncrement = match($type) {
            'ddos_attempt' => 5,
            'brute_force_attempt' => 4,
            'sql_injection_attempt' => 5,
            'xss_attempt' => 4,
            'suspicious_behavior' => 2,
            default => 1
        };
        
        $score += $scoreIncrement;
        Cache::put($key, $score, now()->addHours(24));
        
        // If threat score exceeds threshold, block IP
        if ($score >= 10) {
            $blockedIPs = Cache::get('blocked_ips', []);
            if (!in_array($ip, $blockedIPs)) {
                $blockedIPs[] = $ip;
                Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
                Cache::increment('blocked_ips_count');
                
                // Store additional block information
                Cache::put("block_info:{$ip}", [
                    'blocked_at' => now()->format('Y-m-d H:i:s'),
                    'reason' => $reason,
                    'threat_score' => $score,
                    'trigger_event' => $type,
                    'blocked_by' => 'system'
                ], now()->addDays(7));
                
                // Log the automatic block
                SecurityEvent::create([
                    'event_type' => 'ip_auto_blocked',
                    'icon_class' => 'mdi-shield-lock',
                    'ip_address' => $ip,
                    'details' => "IP automatically blocked due to multiple security violations: {$reason}",
                    'threat_level' => 'high',
                    'user_agent' => request()->userAgent(),
                    'request_details' => [
                        'threat_score' => $score,
                        'block_reason' => $reason,
                        'trigger_event' => $type
                    ]
                ]);
            }
        }
    }
} 