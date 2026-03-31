<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SecurityEvent;
use Carbon\Carbon;

class CleanOldLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean-old {--days=90 : The number of days to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old activity logs, error logs, and security events from the database to save space, preserving critical data.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Starting log cleanup for records older than {$days} days ({$cutoffDate->format('Y-m-d H:i:s')})...");

        // 1. Clean Activity Logs (Delete all older than X days)
        $deletedActivities = DB::table('activity_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
        $this->info("- Deleted {$deletedActivities} old activity logs.");

        // 2. Clean Error Logs (Delete older than X days, EXCEPT level = 'error')
        $deletedErrors = DB::table('error_logs')
            ->where('created_at', '<', $cutoffDate)
            ->where('level', '!=', 'error')
            ->delete();
        $this->info("- Deleted {$deletedErrors} old non-critical error logs (info/warning).");

        // 3. Clean Security Events (Delete older than X days, EXCEPT threat_level = 'high')
        $deletedSecurityEvents = SecurityEvent::where('created_at', '<', $cutoffDate)
            ->where('threat_level', '!=', 'high')
            ->delete();
        $this->info("- Deleted {$deletedSecurityEvents} old non-high security events.");

        $this->info("Log cleanup completed successfully.");

        // Log this action itself to error_logs (as info) or activity_logs so admins know it ran
        DB::table('error_logs')->insert([
            'user_id' => null,
            'username' => 'System',
            'level' => 'info',
            'message' => "Automated Log Cleanup: Deleted {$deletedActivities} activities, {$deletedErrors} minor errors, {$deletedSecurityEvents} low/med security events older than {$days} days.",
            'file' => __FILE__,
            'line' => __LINE__,
            'trace' => 'N/A',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'CLI artisan system:cron',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return 0;
    }
}
