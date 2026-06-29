<?php

namespace App\Notifications\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;

/**
 * Canal custom de Laravel Notifications. Se agrega al array que devuelve
 * via() de cualquier Notification (junto con 'database', sin reemplazarlo).
 * Laravel lo resuelve automaticamente por el nombre de la clase -- no
 * requiere registro adicional en ningun service provider.
 *
 * Si la notificacion no define toFcm(), o el usuario no tiene fcm_token
 * guardado (porque nunca abrio la app, o no dio permiso de notificaciones),
 * simplemente no se envia nada -- no rompe el resto del flujo de
 * notificaciones (la fila en 'database' se sigue creando normal).
 */
class FcmChannel
{
    public function __construct(protected FcmService $fcm)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $token = $notifiable->fcm_token ?? null;

        if (! $token) {
            return;
        }

        $payload = $notification->toFcm($notifiable);

        $this->fcm->sendToToken(
            $token,
            $payload['title'],
            $payload['body'],
            $payload['data'] ?? [],
        );
    }
}
