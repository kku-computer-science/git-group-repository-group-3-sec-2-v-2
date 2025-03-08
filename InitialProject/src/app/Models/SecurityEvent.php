<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'icon_class',
        'user_id',
        'ip_address',
        'details',
        'threat_level',
        'user_agent',
        'location',
        'request_details',
        'additional_data'
    ];

    protected $casts = [
        'request_details' => 'array',
        'additional_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
