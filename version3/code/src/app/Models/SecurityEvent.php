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

    /**
     * Get the user that owns the security event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a CSS class based on the threat level
     */
    public function getThreatLevelClass()
    {
        switch($this->threat_level) {
            case 1:
                return 'bg-success text-white';
            case 2:
                return 'bg-info text-white';
            case 3:
                return 'bg-warning text-dark';
            case 4:
                return 'bg-danger text-white';
            case 5:
                return 'bg-dark text-white';
            default:
                return 'bg-secondary text-white';
        }
    }
}
