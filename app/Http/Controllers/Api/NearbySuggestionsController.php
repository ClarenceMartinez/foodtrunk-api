<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\SmartNearbyAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NearbySuggestionsController extends Controller
{
    public function __construct(
        protected SmartNearbyAlertService $smartAlerts,
    ) {
    }

    /**
     * GET /api/me/nearby-suggestions
     * Seccion "Near you right now" del home. A diferencia del envio de
     * push (NotifyFoodTruckFollowers), este es un endpoint de LECTURA:
     * no aplica el limite diario de alertas ni el "ya le avise hoy de
     * este food truck" (esas reglas existen para no saturar con push
     * notifications, no para esconder informacion cuando el usuario
     * mismo abre la app y pregunta "que hay cerca de mi").
     *
     * Cubre food trucks que el usuario sigue (con o sin notificaciones
     * activas) que tengan una promocion activa y elegible dentro del
     * radio de Smart Nearby Alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->notificationSettings();

        if (! $settings->allowsSmartPromotionAlerts() || $user->lastLocation === null) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $followedFoodTrucks = $user->favoriteFoodTrucks()
            ->withoutGlobalScopes()
            ->with('locations')
            ->get();

        $suggestions = [];

        foreach ($followedFoodTrucks as $foodTruck) {
            $distance = $this->smartAlerts->distanceToNearestLocation($user, $foodTruck);

            if ($distance === null || $distance > SmartNearbyAlertService::MAX_DISTANCE_MILES) {
                continue;
            }

            $activePromotion = Promotion::withoutGlobalScopes()
                ->where('food_truck_id', $foodTruck->id)
                ->whereIn('status', ['active', 'scheduled'])
                ->latest()
                ->get()
                ->first(fn ($promotion) => $this->smartAlerts->isPromotionEligible($promotion));

            if ($activePromotion === null) {
                continue;
            }

            $suggestions[] = [
                'type' => 'smart_nearby_promotion',
                'food_truck_id' => $foodTruck->id,
                'food_truck_name' => $foodTruck->name,
                'promotion_id' => $activePromotion->id,
                'title' => 'Special deal near you',
                'message' => "{$foodTruck->name} has a new promotion ".round($distance, 1).' miles away.',
                'distance_miles' => round($distance, 1),
            ];
        }

        usort($suggestions, fn ($a, $b) => $a['distance_miles'] <=> $b['distance_miles']);

        return response()->json(['success' => true, 'data' => $suggestions]);
    }
}
