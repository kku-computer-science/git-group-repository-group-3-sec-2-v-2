<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\SecurityMonitoringService;

class TestSecuritySystem extends Command
{
    protected $signature = 'security:test {feature? : The feature to test (ddos/sql/xss/brute)}';
    protected $description = 'Test security monitoring features';

    protected $securityService;

    public function __construct(SecurityMonitoringService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    public function handle()
    {
        $feature = $this->argument('feature');
        
        if (!$feature) {
            $feature = $this->choice(
                'Which security feature would you like to test?',
                ['ddos', 'sql', 'xss', 'brute', 'all'],
                4
            );
        }

        $this->info("Starting security test for: " . strtoupper($feature));
        $this->info("----------------------------------------");

        switch ($feature) {
            case 'ddos':
                $this->testDDoS();
                break;
            case 'sql':
                $this->testSQLInjection();
                break;
            case 'xss':
                $this->testXSS();
                break;
            case 'brute':
                $this->testBruteForce();
                break;
            case 'all':
                $this->testDDoS();
                $this->testSQLInjection();
                $this->testXSS();
                $this->testBruteForce();
                break;
        }
    }

    protected function testDDoS()
    {
        $this->info("Testing DDoS Protection...");
        $testIP = '192.168.1.1';
        
        // Simulate 120 requests in a minute
        for ($i = 0; $i < 120; $i++) {
            $result = $this->securityService->monitorRequestRate($testIP);
            if ($result) {
                $this->info("✓ DDoS Protection: Detected high request rate after $i requests");
                return;
            }
        }
        
        $this->error("✗ DDoS Protection: Failed to detect high request rate");
    }

    protected function testSQLInjection()
    {
        $this->info("Testing SQL Injection Detection...");
        $testIP = '192.168.1.2';
        
        $testCases = [
            "1' OR '1'='1",
            "'; DROP TABLE users; --",
            "1 UNION SELECT * FROM users",
            "admin' --",
        ];

        $detected = 0;
        foreach ($testCases as $test) {
            if ($this->securityService->detectSQLInjection($test, $testIP)) {
                $this->info("✓ Detected SQL injection pattern: " . $test);
                $detected++;
            } else {
                $this->error("✗ Failed to detect SQL injection pattern: " . $test);
            }
        }

        $this->info("SQL Injection Detection Rate: $detected/" . count($testCases));
    }

    protected function testXSS()
    {
        $this->info("Testing XSS Detection...");
        $testIP = '192.168.1.3';
        
        $testCases = [
            "<script>alert('xss')</script>",
            "javascript:alert(1)",
            "<img src='x' onerror='alert(1)'>",
            "<svg onload='alert(1)'>",
        ];

        $detected = 0;
        foreach ($testCases as $test) {
            if ($this->securityService->detectXSS($test, $testIP)) {
                $this->info("✓ Detected XSS pattern: " . $test);
                $detected++;
            } else {
                $this->error("✗ Failed to detect XSS pattern: " . $test);
            }
        }

        $this->info("XSS Detection Rate: $detected/" . count($testCases));
    }

    protected function testBruteForce()
    {
        $this->info("Testing Brute Force Detection...");
        $testIP = '192.168.1.4';
        
        // Simulate 6 failed login attempts
        for ($i = 1; $i <= 6; $i++) {
            $result = $this->securityService->monitorFailedLogins($testIP, 'test_user');
            if ($result) {
                $this->info("✓ Brute Force Protection: Detected after $i failed attempts");
                return;
            }
            $this->info("Simulated failed login attempt: $i");
            sleep(1); // Wait 1 second between attempts
        }
        
        $this->error("✗ Brute Force Protection: Failed to detect multiple login attempts");
    }
} 