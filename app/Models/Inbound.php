<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the server associated with the inbound.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_ip', 'address');
    }

    /**
     * The mutator for the 'username' attribute
     */
    public function setUsernameAttribute($value)
    {
           $this->attributes['username'] = strtolower($value);
    }

    /**
     * The mutator for the 'traffic_limit' attribute
     */
    public function setTrafficLimitAttribute($value)
    {
        // Modify the value before saving it
        $this->attributes['traffic_limit'] = isset($value) ? number_format($value, 2) : null;
    }

    /**
     * The mutator for the 'remaining_traffic' attribute
     */
    public function setRemainingTrafficAttribute($value)
    {
        // Modify the value before saving it
        $this->attributes['remaining_traffic'] = isset($value) ? number_format($value, 2) : null;
    }

    public function outline(): HasOne
    {
        return $this->hasOne(Outline::class);
    }
}
