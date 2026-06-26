<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    public const TYPE_PROMOTION_CREATED = 'promotion_created';

    public const TYPE_EVENT_CREATED = 'event_created';

    public const TYPE_LOCATION_CHANGED = 'location_changed';

    public const TYPE_SMART_NEARBY_PROMOTION = 'smart_nearby_promotion';

    public const TYPE_SMART_NEARBY_EVENT = 'smart_nearby_event';

    public const TYPE_SMART_NEARBY_LOCATION_UPDATE = 'smart_nearby_location_update';

    protected $fillable = [
        'user_id',
        'food_truck_id',
        'type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodTruck(): BelongsTo
    {
        return $this->belongsTo(FoodTruck::class);
    }
}
