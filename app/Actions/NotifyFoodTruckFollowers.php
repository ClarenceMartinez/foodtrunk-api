<?php

namespace App\Actions;

use App\Models\FoodTruck;
use App\Models\NotificationLog;
use App\Models\Promotion;
use App\Notifications\FoodTruckUpdateNotification;
use App\Notifications\SmartNearbyAlertNotification;
use App\Services\SmartNearbyAlertService;
use Illuminate\Support\Facades\Notification;

/**
 * Implementa el pseudocodigo de la seccion 1.7 del doc de Smart Nearby
 * Alerts:
 *
 *   Escenario A: el usuario tiene notifications_enabled=true para este
 *     food truck -> notificacion normal (como ya funcionaba).
 *   Escenario B: el usuario las apago, PERO tiene Smart Nearby Alerts
 *     activado, esta cerca, y no excedio sus limites diarios -> alerta
 *     inteligente.
 *
 * El parametro $promotion es opcional porque el Escenario B, tal como
 * esta especificado en el doc, solo aplica a promociones por ahora
 * (events/location todavia no tienen su propia regla de elegibilidad
 * definida mas alla del nombre del tipo). Si no se pasa $promotion,
 * el usuario con notificaciones apagadas simplemente no recibe nada
 * -- igual que antes de este feature.
 */
class NotifyFoodTruckFollowers
{
    public function __construct(
        protected SmartNearbyAlertService $smartAlerts,
    ) {
    }

    public function execute(
        FoodTruck $foodTruck,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?Promotion $promotion = null,
    ): void {
        $followers = $foodTruck->followers()->get();

        if ($followers->isEmpty()) {
            return;
        }

        $normalRecipients = [];

        foreach ($followers as $user) {
            $notificationsEnabled = (bool) $user->pivot->notifications_enabled;

            // Escenario A.
            if ($notificationsEnabled) {
                $normalRecipients[] = $user;

                NotificationLog::create([
                    'user_id' => $user->id,
                    'food_truck_id' => $foodTruck->id,
                    'type' => $type,
                    'sent_at' => now(),
                ]);

                continue;
            }

            // Escenario B (solo promociones por ahora).
            if ($promotion === null) {
                continue;
            }

            if (! $this->smartAlerts->canSendSmartPromotionAlert($user, $foodTruck, $promotion)) {
                continue;
            }

            $distance = $this->smartAlerts->distanceToNearestLocation($user, $foodTruck) ?? 0.0;
            $firstName = trim(strtok($user->name, ' ')) ?: $user->name;

            $user->notify(new SmartNearbyAlertNotification($foodTruck, $promotion, $distance, $firstName));

            NotificationLog::create([
                'user_id' => $user->id,
                'food_truck_id' => $foodTruck->id,
                'type' => 'smart_nearby_promotion',
                'sent_at' => now(),
            ]);
        }

        if (! empty($normalRecipients)) {
            Notification::send(
                $normalRecipients,
                new FoodTruckUpdateNotification($foodTruck, $type, $title, $message, $data)
            );
        }
    }
}
