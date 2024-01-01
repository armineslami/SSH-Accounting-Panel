<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemInfo extends Model
{
    use HasFactory;

    protected $table = 'system_info';

    protected $fillable = [
        'cpuUsage',
        'memory',
        'memoryUsage',
        'swap',
        'swapUsage',
        'disk',
        'diskUsage',
        'upTime'
    ];
}
