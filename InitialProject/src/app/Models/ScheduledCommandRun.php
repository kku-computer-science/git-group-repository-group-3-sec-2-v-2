<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledCommandRun extends Model
{
    protected $fillable = [
        'scheduled_command_id',
        'executed_command',
        'status',
        'exit_code',
        'output',
        'error_message',
        'ran_via',
        'meta',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function scheduledCommand()
    {
        return $this->belongsTo(ScheduledCommand::class);
    }
}
