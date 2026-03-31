<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ManageBlockedIPs extends Command
{
    protected $signature = 'security:ip 
        {action? : Action to perform (list/remove/clear)}
        {ip? : IP address to remove from blocklist}';
    
    protected $description = 'Manage blocked IP addresses';

    public function handle()
    {
        $action = $this->argument('action');
        
        if (!$action) {
            $action = $this->choice(
                'What would you like to do?',
                ['list', 'remove', 'clear'],
                0
            );
        }

        switch ($action) {
            case 'list':
                $this->listBlockedIPs();
                break;
            
            case 'remove':
                $ip = $this->argument('ip');
                if (!$ip) {
                    $ip = $this->ask('Enter the IP address to unblock');
                }
                $this->removeIP($ip);
                break;
            
            case 'clear':
                if ($this->confirm('Are you sure you want to clear all blocked IPs?')) {
                    $this->clearBlockList();
                }
                break;
        }
    }

    protected function listBlockedIPs()
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        
        if (empty($blockedIPs)) {
            $this->info('No IPs are currently blocked.');
            return;
        }

        $headers = ['IP Address', 'Blocked Since', 'Threat Score', 'Reason'];
        $rows = [];

        foreach ($blockedIPs as $ip) {
            $blockInfo = Cache::get("block_info:{$ip}", []);
            $threatScore = Cache::get("threat_score:{$ip}", 0);
            
            $rows[] = [
                $ip,
                $blockInfo['blocked_at'] ?? 'Unknown',
                $threatScore,
                $blockInfo['reason'] ?? 'N/A'
            ];
        }

        $this->table($headers, $rows);
        $this->info("Total blocked IPs: " . count($blockedIPs));
    }

    protected function removeIP($ip)
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        
        if (!in_array($ip, $blockedIPs)) {
            $this->error("IP address {$ip} is not blocked.");
            return;
        }

        $blockedIPs = array_diff($blockedIPs, [$ip]);
        Cache::put('blocked_ips', $blockedIPs, now()->addDays(7));
        Cache::decrement('blocked_ips_count');
        Cache::forget("block_info:{$ip}");
        Cache::forget("threat_score:{$ip}");

        $this->info("Successfully unblocked IP address: {$ip}");
    }

    protected function clearBlockList()
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        
        foreach ($blockedIPs as $ip) {
            Cache::forget("block_info:{$ip}");
            Cache::forget("threat_score:{$ip}");
        }

        Cache::put('blocked_ips', [], now()->addDays(7));
        Cache::put('blocked_ips_count', 0, now()->addDays(7));

        $this->info('Successfully cleared all blocked IPs.');
    }
} 