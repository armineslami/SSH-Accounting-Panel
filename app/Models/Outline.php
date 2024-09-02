<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outline extends Model
{
    use HasFactory;

    protected $fillable = [
        'outline_id',
        'key_name',
        'key',
        'inbound_id',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class);
    }
}
