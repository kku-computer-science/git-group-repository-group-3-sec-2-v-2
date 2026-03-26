<?php
   
namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\ScheduledCommandService;
    
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DemoCron::class,
        // \App\Console\Commands\FetchScopusData::class,
        Commands\ScopusFetchCommand::class,
        Commands\TestSecuritySystem::class,
        Commands\ManageBlockedIPs::class,
        Commands\CleanOldLogsCommand::class,
    ];
     
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $scheduledCommandService = app(ScheduledCommandService::class);

        foreach ($scheduledCommandService->getSchedulerDefinitions() as $definition) {
            $commandToRun = isset($definition['id'])
                ? 'scheduled-command:run ' . $definition['id']
                : $definition['command'];

            $schedule->command($commandToRun)
                ->cron($definition['cron_expression'])
                ->timezone($definition['timezone'] ?? 'Asia/Bangkok');
        }
    }
     
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
     
        require base_path('routes/console.php');
    }
}
