<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'inbound_traffic_limit',
        'inbound_active_days',
        'inbound_max_login',
//        'inbound_server_ips'
    ];
}
