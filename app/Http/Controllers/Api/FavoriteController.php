<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodTruckResource;
use App\Models\FoodTruck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * GET /api/consumer/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favoriteFoodTrucks()
            ->withoutGlobalScopes()
            ->with('company:id,name')
            ->get();

        return response()->json(['data' => FoodTruckResource::collection($favorites)]);
    }

    /**
     * POST /api/consumer/favorites/{foodTruck}
     * Seguir un food truck (= agregar a favoritos). Las notificaciones
     * quedan activas por defecto al seguir.
     */
    public function store(Request $request, FoodTruck $foodTruck): JsonResponse
    {
        $request->user()->favoriteFoodTrucks()->syncWithoutDetaching([
            $foodTruck->id => ['notifications_enabled' => true],
        ]);

        return response()->json([
            'message' => __('messages.favorite.added'),
            'data' => [
                'food_truck_id' => $foodTruck->id,
                'is_following' => true,
                'notifications_enabled' => true,
            ],
        ], 201);
    }

    /**
     * DELETE /api/consumer/favorites/{foodTruck}
     * Dejar de seguir. Al hacerlo, ya no debe recibir mas notificaciones
     * de este food truck (la fila del pivot desaparece por completo).
     */
    public function destroy(Request $request, FoodTruck $foodTruck): JsonResponse
    {
        $request->user()->favoriteFoodTrucks()->detach($foodTruck->id);

        return response()->json([
            'message' => __('messages.favorite.removed'),
            'data' => [
                'food_truck_id' => $foodTruck->id,
                'is_following' => false,
            ],
        ]);
    }

    /**
     * GET /api/consumer/favorites/{foodTruck}/status
     * Usado en el detalle del food truck para saber si mostrar
     * "Follow" o "Following" (HU-04).
     */
    public function status(Request $request, FoodTruck $foodTruck): JsonResponse
    {
        $favorite = $request->user()->favoriteFoodTrucks()
            ->where('food_trucks.id', $foodTruck->id)
            ->first();

        return response()->json([
            'data' => [
                'food_truck_id' => $foodTruck->id,
                'is_following' => $favorite !== null,
                'notifications_enabled' => $favorite?->pivot->notifications_enabled ?? false,
            ],
        ]);
    }

    /**
     * PATCH /api/consumer/favorites/{foodTruck}/notifications
     * Activar/desactivar notificaciones SIN dejar de seguir (HU-05).
     */
    public function updateNotifications(Request $request, FoodTruck $foodTruck): JsonResponse
    {
        $validated = $request->validate([
            'notifications_enabled' => 'required|boolean',
        ]);

        $favorite = $request->user()->favoriteFoodTrucks()
            ->where('food_trucks.id', $foodTruck->id)
            ->first();

        abort_if($favorite === null, 404, 'No estas siguiendo este food truck.');

        $request->user()->favoriteFoodTrucks()->updateExistingPivot($foodTruck->id, [
            'notifications_enabled' => $validated['notifications_enabled'],
        ]);

        return response()->json([
            'message' => 'Preferencia de notificaciones actualizada',
            'data' => [
                'food_truck_id' => $foodTruck->id,
                'is_following' => true,
                'notifications_enabled' => $validated['notifications_enabled'],
            ],
        ]);
    }
}
