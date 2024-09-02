<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'address',
        'port',
        'udp_port',
        'outline_api_url'
    ];

    /**
     * Get the inbounds associated with the server.
     */
    public function inbounds(): HasMany
    {
        return $this->hasMany(Inbound::class, 'server_ip', 'address');
    }
}
