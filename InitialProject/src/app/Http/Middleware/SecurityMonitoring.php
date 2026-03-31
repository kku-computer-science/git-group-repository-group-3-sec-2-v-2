<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SecurityMonitoringService;
use Illuminate\Support\Facades\Cache;

class SecurityMonitoring
{
    protected $securityService;

    public function __construct(SecurityMonitoringService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        
        // Check if IP is blocked
        $blockedIPs = Cache::get('blocked_ips', []);
        if (in_array($ip, $blockedIPs)) {
            abort(403, 'Your IP address has been blocked due to suspicious activity.');
        }

        // Monitor request rate for DDoS
        if ($this->securityService->monitorRequestRate($ip)) {
            abort(429, 'Too many requests detected. Please try again later.');
        }

        // Check for SQL injection attempts in request parameters
        foreach ($request->all() as $key => $value) {
            if (is_string($value) && $this->securityService->detectSQLInjection($value, $ip)) {
                abort(403, 'Suspicious request detected.');
            }
        }

        // Check for XSS attempts
        foreach ($request->all() as $key => $value) {
            if (is_string($value) && $this->securityService->detectXSS($value, $ip)) {
                abort(403, 'Suspicious request detected.');
            }
        }

        // Monitor for suspicious behavior
        if ($this->securityService->detectSuspiciousBehavior($ip, $request->userAgent(), $request->all())) {
            // Don't block immediately, just monitor and log
            // The SecurityMonitoringService will handle threat scoring
        }

        return $next($request);
    }
} 