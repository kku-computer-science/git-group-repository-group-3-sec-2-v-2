<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'level',
        'message',
        'context',
        'file',
        'line',
        'stack_trace',
        'ip_address',
        'user_id',
        'username',
        'url',
        'method',
        'user_agent'
    ];
    
    /**
     * Get the user that caused the error.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 