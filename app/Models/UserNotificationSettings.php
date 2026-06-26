<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationSettings extends Model
{
    protected $fillable = [
        'user_id',
        'push_notifications_enabled',
        'smart_nearby_alerts_enabled',
        'location_alerts_enabled',
        'promotion_alerts_enabled',
        'max_alerts_per_day',
    ];

    protected $casts = [
        'push_notifications_enabled' => 'boolean',
        'smart_nearby_alerts_enabled' => 'boolean',
        'location_alerts_enabled' => 'boolean',
        'promotion_alerts_enabled' => 'boolean',
        'max_alerts_per_day' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * true solo si TODAS las condiciones para un smart alert de
     * promocion estan activas. Centraliza la regla en un solo lugar
     * en vez de repetir 3 ifs en cada sitio que lo necesite.
     */
    public function allowsSmartPromotionAlerts(): bool
    {
        return $this->push_notifications_enabled
            && $this->smart_nearby_alerts_enabled
            && $this->location_alerts_enabled
            && $this->promotion_alerts_enabled;
    }
}
