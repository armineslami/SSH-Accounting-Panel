<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbound extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'is_active',
        'traffic_limit',
        'remaining_traffic',
        'max_login',
        'server_ip',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
//        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
//        'password' => 'hashed',
    ];

    /**
     * Default value for attributes
     *
     * @var int[]
     */
    protected $attributes = [
        'max_login' => 1,
        'is_active' => '1'
    ];


    /**
     * The mutator for the 'traffic_limit' attribute
     */
    public function setTrafficLimitAttribute($value)
    {
        // Modify the value before saving it
        $this->attributes['traffic_limit'] = number_format($value, 2);
    }
}
