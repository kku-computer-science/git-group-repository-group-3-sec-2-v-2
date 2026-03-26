<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledCommand extends Model
{
    protected $fillable = [
        'name',
        'command',
        'description',
        'cron_expression',
        'timezone',
        'is_enabled',
        'display_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function runs()
    {
        return $this->hasMany(ScheduledCommandRun::class);
    }
}
