<?php

namespace App\Notifications;

use App\Models\FoodTruck;
use App\Notifications\Channels\FcmChannel;
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
 *
 * El canal FcmChannel manda, ademas, el push real al telefono via
 * Firebase Cloud Messaging. Si el usuario no tiene fcm_token guardado
 * (no abrio la app o no dio permiso), el canal simplemente no hace nada
 * y la fila en "database" se crea igual.
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
        return ['database', FcmChannel::class];
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

    /**
     * Payload del push. El "data" se manda como strings (FCM lo exige)
     * para que la app Flutter pueda usarlo al navegar al tocar la
     * notificacion (por ejemplo, abrir directo el detail screen del
     * food truck via food_truck_id).
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'data' => array_merge([
                'type' => $this->type,
                'food_truck_id' => (string) $this->foodTruck->id,
            ], array_map('strval', $this->data)),
        ];
    }
}
