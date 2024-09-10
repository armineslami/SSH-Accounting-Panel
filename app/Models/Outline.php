<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outline extends Model
{
    use HasFactory;

    protected $fillable = [
        'outline_id',
        'key_name',
        'key',
        'inbound_id',
        'server_id',
        'traffic_usage'
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class);
    }

    public function server(): BelongsTo
    {
        return $this->BelongsTo(Server::class);
    }
}
