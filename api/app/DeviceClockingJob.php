<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceClockingJob extends Model
{
    protected $table = 'device_clocking_jobs';

    protected $fillable = [
        'payload', 'image_path', 'status', 'attempts',
        'error_log', 'started_at', 'failed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempts' => 'integer'
    ];
}