<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'last_seen_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Una ubicacion "fresca" para efectos de Smart Alerts. Si el usuario
     * no abre la app en dias, no tiene sentido seguir asumiendo que
     * sigue parado en el mismo punto.
     */
    public function isFresh(int $maxAgeMinutes = 120): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->diffInMinutes(now()) <= $maxAgeMinutes;
    }
}
