<?php

namespace App\Http\Controllers\Api;

use App\Actions\NotifyFoodTruckFollowers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\FoodTruck;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * GET /api/company/food-trucks/{foodTruck}/locations
     */
    public function index(FoodTruck $foodTruck): JsonResponse
    {
        return response()->json([
            'data' => LocationResource::collection($foodTruck->locations()->latest()->get()),
        ]);
    }

    /**
     * POST /api/company/food-trucks/{foodTruck}/locations
     */
    public function store(StoreLocationRequest $request, FoodTruck $foodTruck): JsonResponse
    {
        $location = $foodTruck->locations()->create($request->validated());

        // HU-08: notificar a los seguidores cuando se agrega una ubicación
        // nueva. Solo en store() (no en update()) para evitar notificar
        // por cada corrección menor de dirección/horario ya existente.
        app(NotifyFoodTruckFollowers::class)->execute(
            $foodTruck,
            'location_updated',
            "{$foodTruck->name} is now near you in {$location->city}",
            $location->address,
            ['location_id' => $location->id],
        );

        return response()->json([
            'data' => new LocationResource($location),
            'message' => __('messages.location.created'),
        ], 201);
    }

    /**
     * GET /api/company/food-trucks/{foodTruck}/locations/{location}
     */
    public function show(FoodTruck $foodTruck, Location $location): JsonResponse
    {
        $this->ensureLocationBelongsToFoodTruck($foodTruck, $location);

        return response()->json(['data' => new LocationResource($location)]);
    }

    /**
     * PUT/PATCH /api/company/food-trucks/{foodTruck}/locations/{location}
     */
    public function update(UpdateLocationRequest $request, FoodTruck $foodTruck, Location $location): JsonResponse
    {
        $this->ensureLocationBelongsToFoodTruck($foodTruck, $location);

        $location->update($request->validated());

        return response()->json([
            'data' => new LocationResource($location),
            'message' => __('messages.location.updated'),
        ]);
    }

    /**
     * DELETE /api/company/food-trucks/{foodTruck}/locations/{location}
     */
    public function destroy(FoodTruck $foodTruck, Location $location): JsonResponse
    {
        $this->ensureLocationBelongsToFoodTruck($foodTruck, $location);

        $location->delete();

        return response()->json(['message' => __('messages.location.deleted')]);
    }

    /**
     * Evita que alguien acceda a /food-trucks/1/locations/99 si la
     * ubicación 99 en realidad pertenece al food truck 2.
     */
    private function ensureLocationBelongsToFoodTruck(FoodTruck $foodTruck, Location $location): void
    {
        abort_if($location->food_truck_id !== $foodTruck->id, 404, __('messages.location.not_found'));
    }
}
