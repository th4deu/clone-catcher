<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloneLog extends Model
{
    protected $fillable = [
        'session_id',
        'domain',
        'url',
        'client_ip',
        'client_user_agent',
        'referrer',
        'screen_resolution',
        'language',
        'requests',
        'client_timestamp',
    ];

    protected $casts = [
        'requests' => 'array',
        'client_timestamp' => 'datetime',
    ];

    public function getRequestCountAttribute()
    {
        return is_array($this->requests) ? count($this->requests) : 0;
    }

    public function getHttpRequestsAttribute()
    {
        if (!is_array($this->requests)) {
            return [];
        }

        return collect($this->requests)->filter(function ($request) {
            return in_array($request['type'] ?? '', ['fetch', 'xhr', 'fetch_response', 'xhr_response']);
        });
    }
}
