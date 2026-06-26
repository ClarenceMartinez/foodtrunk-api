<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\FoodTruck;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * GET /api/company/food-trucks/{foodTruck}/menus
     */
    public function index(FoodTruck $foodTruck): JsonResponse
    {
        $menus = $foodTruck->menus()->withCount('items')->latest()->get();

        return response()->json(['data' => MenuResource::collection($menus)]);
    }

    /**
     * POST /api/company/food-trucks/{foodTruck}/menus
     */
    public function store(StoreMenuRequest $request, FoodTruck $foodTruck): JsonResponse
    {
        $menu = $foodTruck->menus()->create($request->validated());

        return response()->json([
            'data' => new MenuResource($menu),
            'message' => __('messages.menu.created'),
        ], 201);
    }

    /**
     * GET /api/company/food-trucks/{foodTruck}/menus/{menu}
     */
    public function show(FoodTruck $foodTruck, Menu $menu): JsonResponse
    {
        $this->ensureMenuBelongsToFoodTruck($foodTruck, $menu);

        $menu->load('items');

        return response()->json(['data' => new MenuResource($menu)]);
    }

    /**
     * PUT/PATCH /api/company/food-trucks/{foodTruck}/menus/{menu}
     */
    public function update(UpdateMenuRequest $request, FoodTruck $foodTruck, Menu $menu): JsonResponse
    {
        $this->ensureMenuBelongsToFoodTruck($foodTruck, $menu);

        $menu->update($request->validated());

        return response()->json([
            'data' => new MenuResource($menu),
            'message' => __('messages.menu.updated'),
        ]);
    }

    /**
     * DELETE /api/company/food-trucks/{foodTruck}/menus/{menu}
     */
    public function destroy(FoodTruck $foodTruck, Menu $menu): JsonResponse
    {
        $this->ensureMenuBelongsToFoodTruck($foodTruck, $menu);

        $menu->delete();

        return response()->json(['message' => __('messages.menu.deleted')]);
    }

    private function ensureMenuBelongsToFoodTruck(FoodTruck $foodTruck, Menu $menu): void
    {
        abort_if($menu->food_truck_id !== $foodTruck->id, 404, __('messages.menu.not_found'));
    }
}
