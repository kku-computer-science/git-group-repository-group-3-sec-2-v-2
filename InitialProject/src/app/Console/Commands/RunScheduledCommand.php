<?php

namespace App\Console\Commands;

use App\Models\ScheduledCommand;
use App\Models\ScheduledCommandRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RunScheduledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduled-command:run {scheduledCommandId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a configured scheduled command and store its execution result.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!Schema::hasTable('scheduled_commands') || !Schema::hasTable('scheduled_command_runs')) {
            $this->error('Scheduled command tables are not ready.');

            return 1;
        }

        $scheduledCommand = ScheduledCommand::find($this->argument('scheduledCommandId'));

        if (!$scheduledCommand) {
            $this->error('Scheduled command configuration not found.');

            return 1;
        }

        $run = ScheduledCommandRun::create([
            'scheduled_command_id' => $scheduledCommand->id,
            'executed_command' => $scheduledCommand->command,
            'status' => $scheduledCommand->is_enabled ? 'running' : 'skipped',
            'ran_via' => 'scheduler',
            'meta' => [
                'cron_expression' => $scheduledCommand->cron_expression,
                'timezone' => $scheduledCommand->timezone,
            ],
            'started_at' => now(),
        ]);

        if (!$scheduledCommand->is_enabled) {
            $run->update([
                'status' => 'skipped',
                'output' => 'Command is disabled and was skipped.',
                'finished_at' => now(),
                'exit_code' => 0,
            ]);

            return 0;
        }

        try {
            $exitCode = Artisan::call($scheduledCommand->command);
            $output = trim(Artisan::output());

            $run->update([
                'status' => $exitCode === 0 ? 'success' : 'failed',
                'exit_code' => $exitCode,
                'output' => $output !== '' ? $output : 'Command completed without console output.',
                'finished_at' => now(),
            ]);

            if ($output !== '') {
                $this->line($output);
            }

            return $exitCode;
        } catch (Throwable $throwable) {
            Log::error('Scheduled command execution failed.', [
                'scheduled_command_id' => $scheduledCommand->id,
                'command' => $scheduledCommand->command,
                'message' => $throwable->getMessage(),
            ]);

            $run->update([
                'status' => 'failed',
                'exit_code' => 1,
                'error_message' => $throwable->getMessage(),
                'output' => trim(Artisan::output()) ?: 'Command terminated with an exception.',
                'finished_at' => now(),
            ]);

            $this->error($throwable->getMessage());

            return 1;
        }
    }
}
