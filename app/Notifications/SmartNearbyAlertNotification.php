<?php

namespace App\Notifications;

use App\Models\FoodTruck;
use App\Models\Promotion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Smart Nearby Alert (seccion 1.11 del doc): el mensaje es personalizado
 * con el nombre del usuario y la distancia, a diferencia de la
 * notificacion "normal" de FoodTruckUpdateNotification.
 */
class SmartNearbyAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected FoodTruck $foodTruck,
        protected Promotion $promotion,
        protected float $distanceMiles,
        protected string $userFirstName,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $distance = round($this->distanceMiles, 1);

        return [
            'type' => 'smart_nearby_promotion',
            'title' => 'Hey '.$this->userFirstName.', tenemos algo novedoso para ti',
            'message' => "{$this->foodTruck->name} acaba de lanzar una promoción y estás a solo {$distance} millas.",
            'food_truck_id' => $this->foodTruck->id,
            'food_truck_name' => $this->foodTruck->name,
            'food_truck_logo_url' => $this->foodTruck->logo_url,
            'promotion_id' => $this->promotion->id,
            'distance_miles' => $distance,
        ];
    }
}
