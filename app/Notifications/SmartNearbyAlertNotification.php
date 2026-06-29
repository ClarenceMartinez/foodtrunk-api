<?php

namespace App\Notifications;

use App\Models\FoodTruck;
use App\Models\Promotion;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Smart Nearby Alert (seccion 1.11 del doc): el mensaje es personalizado
 * con el nombre del usuario y la distancia, a diferencia de la
 * notificacion "normal" de FoodTruckUpdateNotification.
 *
 * El canal FcmChannel manda, ademas de guardarla en BD, el push real al
 * telefono. Si el usuario no tiene fcm_token guardado, simplemente no
 * se envia nada via FCM (la fila en "database" se crea igual).
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
        return ['database', FcmChannel::class];
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

    public function toFcm(object $notifiable): array
    {
        $distance = round($this->distanceMiles, 1);

        return [
            'title' => 'Hey '.$this->userFirstName.', tenemos algo novedoso para ti',
            'body' => "{$this->foodTruck->name} acaba de lanzar una promoción y estás a solo {$distance} millas.",
            'data' => [
                'type' => 'smart_nearby_promotion',
                'food_truck_id' => (string) $this->foodTruck->id,
                'promotion_id' => (string) $this->promotion->id,
            ],
        ];
    }
}
