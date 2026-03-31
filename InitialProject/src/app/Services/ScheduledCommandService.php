<?php

namespace App\Services;

use App\Models\ScheduledCommand;
use App\Models\ScheduledCommandRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ScheduledCommandService
{
    public function getDefaultCommands(): array
    {
        return [
            [
                'name' => 'Clean Old Logs',
                'command' => 'logs:clean-old --days=90',
                'description' => 'Clean old activity logs, error logs, and security events.',
                'cron_expression' => '0 1 * * *',
                'timezone' => 'Asia/Bangkok',
                'is_enabled' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Fetch Scopus Papers',
                'command' => 'scopus:fetch',
                'description' => 'Fetch new research papers from Scopus for configured researchers.',
                'cron_expression' => '0 2 * * *',
                'timezone' => 'Asia/Bangkok',
                'is_enabled' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Update Paper Citations',
                'command' => 'papers:update-citations',
                'description' => 'Refresh citation counts from OpenAlex for papers with DOI.',
                'cron_expression' => '0 3 * * *',
                'timezone' => 'Asia/Bangkok',
                'is_enabled' => true,
                'display_order' => 3,
            ],
        ];
    }

    public function canManageCommands(): bool
    {
        return Schema::hasTable('scheduled_commands');
    }

    public function canTrackRuns(): bool
    {
        return Schema::hasTable('scheduled_command_runs');
    }

    public function ensureDefaults(): void
    {
        if (!$this->canManageCommands()) {
            return;
        }

        foreach ($this->getDefaultCommands() as $definition) {
            $scheduledCommand = ScheduledCommand::firstOrNew([
                'command' => $definition['command'],
            ]);

            $existingEnabled = $scheduledCommand->exists
                ? $scheduledCommand->is_enabled
                : null;

            $scheduledCommand->fill([
                'name' => $definition['name'],
                'description' => $definition['description'],
                'cron_expression' => $definition['cron_expression'],
                'timezone' => $definition['timezone'],
                'display_order' => $definition['display_order'],
            ]);

            if ($existingEnabled === null) {
                $scheduledCommand->is_enabled = $definition['is_enabled'];
            } else {
                $scheduledCommand->is_enabled = $existingEnabled;
            }

            $scheduledCommand->save();
        }
    }

    public function getSchedulerDefinitions(): Collection
    {
        if (!$this->canManageCommands()) {
            return collect($this->getDefaultCommands());
        }

        $this->ensureDefaults();

        return ScheduledCommand::query()
            ->where('is_enabled', true)
            ->orderBy('display_order')
            ->get()
            ->map(function (ScheduledCommand $scheduledCommand) {
                return [
                    'id' => $scheduledCommand->id,
                    'name' => $scheduledCommand->name,
                    'command' => $scheduledCommand->command,
                    'cron_expression' => $scheduledCommand->cron_expression,
                    'timezone' => $scheduledCommand->timezone,
                ];
            });
    }

    public function getCommandsForDashboard(): Collection
    {
        if (!$this->canManageCommands()) {
            return collect($this->getDefaultCommands())->map(function (array $definition) {
                return (object) array_merge($definition, [
                    'id' => null,
                    'latest_run' => null,
                ]);
            });
        }

        $this->ensureDefaults();

        $commands = ScheduledCommand::query()
            ->orderBy('display_order')
            ->get();

        if ($commands->isEmpty() || !$this->canTrackRuns()) {
            $commands->each(function (ScheduledCommand $scheduledCommand) {
                $scheduledCommand->latest_run = null;
            });

            return $commands;
        }

        $latestRuns = ScheduledCommandRun::query()
            ->whereIn('scheduled_command_id', $commands->pluck('id'))
            ->orderByDesc('started_at')
            ->get()
            ->groupBy('scheduled_command_id')
            ->map(function (Collection $runs) {
                return $runs->first();
            });

        $commands->each(function (ScheduledCommand $scheduledCommand) use ($latestRuns) {
            $scheduledCommand->latest_run = $latestRuns->get($scheduledCommand->id);
        });

        return $commands;
    }

    public function getRecentRuns(int $limit = 10): Collection
    {
        if (!$this->canTrackRuns()) {
            return collect([]);
        }

        return ScheduledCommandRun::query()
            ->with('scheduledCommand')
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }

    public function getTotalRuns(): int
    {
        if (!$this->canTrackRuns()) {
            return 0;
        }

        return ScheduledCommandRun::count();
    }
}
