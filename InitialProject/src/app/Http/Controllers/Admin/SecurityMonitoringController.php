<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SecurityMonitoringController extends Controller
{
    /**
     * Get failed login attempts data for the chart
     */
    public function getFailedLoginsData()
    {
        // Get failed login attempts for the last week
        $failedLogins = DB::table('activity_logs')
            ->where('action_type', 'failed_login')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];
        
        // Format data for the chart
        foreach ($failedLogins as $login) {
            $labels[] = Carbon::parse($login->date)->format('M d');
            $values[] = $login->count;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    /**
     * Get blocked requests data categorized by type
     */
    public function getBlockedRequestsData()
    {
        // Get blocked requests by type
        $blockedRequests = DB::table('security_events')
            ->where('event_type', 'like', '%blocked%')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->get();

        $labels = [];
        $values = [];
        
        // Format data for the chart
        foreach ($blockedRequests as $request) {
            $labels[] = ucwords(str_replace(['blocked_', '_'], ['', ' '], $request->event_type));
            $values[] = $request->count;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    /**
     * Get system load data (CPU, Memory, Disk)
     */
    public function getSystemLoadData()
    {
        // Get CPU usage
        $cpuUsage = $this->getCpuUsage();
        
        // Get memory usage
        $memoryUsage = $this->getMemoryUsage();
        
        // Get disk usage
        $diskUsage = $this->getDiskUsage();

        return response()->json([
            'success' => true,
            'data' => [
                'cpu' => $cpuUsage,
                'memory' => $memoryUsage,
                'disk' => $diskUsage
            ]
        ]);
    }

    /**
     * Get all dashboard data in one call
     */
    public function getDashboardData()
    {
        return response()->json([
            'success' => true,
            'failedLogins' => $this->getFailedLoginsData()->original['data'],
            'blockedRequests' => $this->getBlockedRequestsData()->original['data'],
            'systemLoad' => $this->getSystemLoadData()->original['data'],
            'securityStats' => [
                'total_alerts' => $this->getTotalAlerts(),
                'blocked_ips' => count(Cache::get('blocked_ips', [])),
                'failed_logins' => $this->getRecentFailedLogins()
            ]
        ]);
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $load = sys_getloadavg();
            $cores = (int)shell_exec('nproc');
            return min(100, round(($load[0] / $cores) * 100));
        }
        
        // Fallback for non-Linux systems
        return random_int(20, 80); // Simulated value for development
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            $memory_usage = round($mem[2]/$mem[1]*100);
            return $memory_usage;
        }
        
        // Fallback for non-Linux systems
        return round(memory_get_usage(true) / memory_get_peak_usage(true) * 100);
    }

    /**
     * Get disk usage percentage
     */
    private function getDiskUsage()
    {
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        
        return round(($disk_total - $disk_free) / $disk_total * 100);
    }

    /**
     * Get total number of security alerts
     */
    private function getTotalAlerts()
    {
        return DB::table('security_events')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();
    }

    /**
     * Get number of failed logins in the last 24 hours
     */
    private function getRecentFailedLogins()
    {
        return DB::table('activity_logs')
            ->where('action_type', 'failed_login')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();
    }
} 