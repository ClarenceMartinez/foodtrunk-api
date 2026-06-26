<?php

namespace App\Services;

use App\Models\FoodTruck;
use App\Models\NotificationLog;
use App\Models\Promotion;
use App\Models\User;
use App\Support\Geo;
use Carbon\Carbon;

/**
 * Centraliza TODAS las reglas de negocio de Smart Nearby Alerts
 * (seccion 1.6 del documento) en un solo lugar. Cualquier regla nueva
 * que se agregue despues (fase 4 del doc) deberia vivir aqui, no
 * regada entre el Action y los Jobs.
 */
class SmartNearbyAlertService
{
    /** Seccion 1.6: "Distancia inicial recomendada: 1.5 miles". */
    public const MAX_DISTANCE_MILES = 1.5;

    /** No avisar si la promo expira en menos de esto. */
    public const MIN_MINUTES_BEFORE_EXPIRATION = 30;

    /** Horario permitido para enviar alertas (evitar madrugada). */
    public const ALLOWED_HOUR_START = 7;

    public const ALLOWED_HOUR_END = 23;

    /**
     * Evalua TODAS las condiciones para enviar un smart alert de
     * promocion a este usuario sobre este food truck/promocion.
     * Si cualquiera falla, no se envia nada (regla: "si no se cumple
     * todo, no enviar nada" -- mejor pecar de silencioso que de spam).
     */
    public function canSendSmartPromotionAlert(User $user, FoodTruck $foodTruck, Promotion $promotion): bool
    {
        $settings = $user->notificationSettings();

        if (! $settings->allowsSmartPromotionAlerts()) {
            return false;
        }

        if (! $this->isWithinAllowedHours()) {
            return false;
        }

        if (! $this->isPromotionEligible($promotion)) {
            return false;
        }

        if (! $this->isUserNearFoodTruck($user, $foodTruck)) {
            return false;
        }

        if (! $this->canSendMoreAlertsToday($user, $foodTruck, $settings->max_alerts_per_day)) {
            return false;
        }

        return true;
    }

    /**
     * "No enviar si la promocion ya expiro" + "no enviar si expira en
     * menos de 30 minutos". Asumo que end_date puede venir como fecha
     * (sin hora) o datetime; si es solo fecha, Carbon la interpreta
     * como medianoche de ese dia -- ajusta esto si tu columna end_date
     * realmente guarda hora de cierre del food truck ese dia.
     */
    public function isPromotionEligible(Promotion $promotion): bool
    {
        if ($promotion->status !== 'active' && $promotion->status !== 'scheduled') {
            return false;
        }

        $endsAt = Carbon::parse($promotion->end_date)->endOfDay();

        if ($endsAt->isPast()) {
            return false;
        }

        if (now()->diffInMinutes($endsAt, false) < self::MIN_MINUTES_BEFORE_EXPIRATION) {
            return false;
        }

        return true;
    }

    /**
     * "No enviar si el usuario esta a mas de 1.5 millas" + "no enviar
     * si no existe ubicacion reciente del usuario" + "no enviar si el
     * food truck no tiene ubicacion actual".
     *
     * El food truck puede tener varias ubicaciones guardadas (locations);
     * usamos la mas cercana, igual que en DiscoverController::nearby().
     */
    public function isUserNearFoodTruck(User $user, FoodTruck $foodTruck): bool
    {
        $userLocation = $user->lastLocation;

        if ($userLocation === null || ! $userLocation->isFresh()) {
            return false;
        }

        $activeLocations = $foodTruck->locations()->where('status', 'active')->get();

        if ($activeLocations->isEmpty()) {
            return false;
        }

        foreach ($activeLocations as $location) {
            $distance = Geo::milesBetween(
                $userLocation->latitude,
                $userLocation->longitude,
                $location->latitude,
                $location->longitude,
            );

            if ($distance <= self::MAX_DISTANCE_MILES) {
                return true;
            }
        }

        return false;
    }

    /**
     * Devuelve la distancia a la ubicacion mas cercana del food truck,
     * o null si no hay ubicacion del usuario / del food truck. Se usa
     * para mostrar "0.4 miles away" en /me/nearby-suggestions.
     */
    public function distanceToNearestLocation(User $user, FoodTruck $foodTruck): ?float
    {
        $userLocation = $user->lastLocation;

        if ($userLocation === null) {
            return null;
        }

        $activeLocations = $foodTruck->locations()->where('status', 'active')->get();

        if ($activeLocations->isEmpty()) {
            return null;
        }

        return $activeLocations
            ->map(fn ($location) => Geo::milesBetween(
                $userLocation->latitude,
                $userLocation->longitude,
                $location->latitude,
                $location->longitude,
            ))
            ->min();
    }

    /**
     * "Maximo 3 smart alerts por dia por usuario" + "maximo 1 alerta
     * por food truck por dia". Estos limites aplican SOLO a smart
     * alerts (tipos smart_nearby_*), no a las notificaciones normales
     * de follow, que el usuario pidio explicitamente al no apagarlas.
     */
    public function canSendMoreAlertsToday(User $user, FoodTruck $foodTruck, int $maxPerDay): bool
    {
        $todayCount = NotificationLog::where('user_id', $user->id)
            ->where('type', 'like', 'smart_nearby_%')
            ->whereDate('sent_at', today())
            ->count();

        if ($todayCount >= $maxPerDay) {
            return false;
        }

        $alreadyAlertedThisFoodTruckToday = NotificationLog::where('user_id', $user->id)
            ->where('food_truck_id', $foodTruck->id)
            ->where('type', 'like', 'smart_nearby_%')
            ->whereDate('sent_at', today())
            ->exists();

        return ! $alreadyAlertedThisFoodTruckToday;
    }

    /**
     * "No enviar en horarios inapropiados, por ejemplo de madrugada."
     * Usa la zona horaria configurada en config('app.timezone').
     */
    public function isWithinAllowedHours(): bool
    {
        $hour = now()->hour;

        return $hour >= self::ALLOWED_HOUR_START && $hour < self::ALLOWED_HOUR_END;
    }
}
