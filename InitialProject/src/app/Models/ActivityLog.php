<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'action_type',
        'description',
        'ip_address',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($userId, $action, $description)
    {
        // Extract action type from the action string (first word)
        $actionParts = explode(' ', $action, 2);
        $actionType = $actionParts[0] ?? '';
        
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
} 