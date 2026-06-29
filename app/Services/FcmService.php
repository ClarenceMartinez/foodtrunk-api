<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

/**
 * Envoltorio simple sobre el SDK de Firebase para mandar push notifications
 * via FCM (API HTTP v1). Usa el Service Account JSON descargado desde
 * Firebase Console (Project settings > Service accounts), NO el
 * google-services.json que usa la app Flutter -- son archivos distintos.
 *
 * Nota de version: desde kreait/firebase-php 7.16, CloudMessage::withTarget()
 * quedo deprecado y en 8.x ya no existe -- la sintaxis actual es
 * CloudMessage::new()->toToken()/toTopic()/toCondition().
 */
class FcmService
{
    protected \Kreait\Firebase\Contract\Messaging $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase-service-account.json');

        $factory = (new Factory)->withServiceAccount($credentialsPath);

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Manda un push a un solo dispositivo. Devuelve false (sin lanzar
     * excepcion) si el token ya no es valido -- esto pasa normalmente
     * cuando el usuario desinstalo la app o el token expiro, y no debe
     * tumbar el flujo de notificaciones para el resto de los seguidores.
     *
     * @param array<string, string> $data Solo strings: FCM exige que todos
     *                                    los valores del payload "data" sean
     *                                    string (los convertimos si no).
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $message = CloudMessage::new()
            ->withNotification(FcmNotification::create($title, $body))
            ->withData(array_map('strval', $data))
            ->toToken($token);

        try {
            $this->messaging->send($message);

            return true;
        } catch (NotFound $e) {
            Log::warning("FCM: token invalido o expirado, se omite el envio. token={$token}");

            return false;
        } catch (InvalidMessage $e) {
            Log::error("FCM: mensaje invalido al enviar. error={$e->getMessage()}");

            return false;
        }
    }
}
