<?php

namespace App\Notifications;

use App\Models\FoodTruck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notificacion generica para seguidores de un food truck.
 *
 * $type sugerido (ver doc de requerimientos seccion 9.1):
 *   promotion_created, event_created, location_updated,
 *   food_truck_opened, special_schedule_created
 *
 * Usa el canal "database" nativo de Laravel: se guarda en la tabla
 * "notifications" (polimorfica), que ya funciona porque el modelo User
 * tiene el trait Notifiable. ShouldQueue hace que el envio masivo a
 * muchos seguidores no bloquee el request que crea la promocion/evento
 * (recomendacion tecnica del doc, seccion 10) -- requiere que tengas
 * un queue worker corriendo, o QUEUE_CONNECTION=sync en .env si todavia
 * no configuras colas (funciona igual, solo sin el beneficio async).
 */
class FoodTruckUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected FoodTruck $foodTruck,
        protected string $type,
        protected string $title,
        protected string $message,
        protected array $data = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'food_truck_id' => $this->foodTruck->id,
            'food_truck_name' => $this->foodTruck->name,
            'food_truck_logo_url' => $this->foodTruck->logo_url,
        ], $this->data);
    }
}
