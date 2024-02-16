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
        'bot_token',
        'bot_port',
        'dropbox_client_id',
        'dropbox_client_secret',
        'dropbox_token',
        'dropbox_refresh_token',
        'dropbox_token_expire_date',
        'app_inbound_bandwidth_check_interval',
        'app_update_check_interval'
    ];
}
