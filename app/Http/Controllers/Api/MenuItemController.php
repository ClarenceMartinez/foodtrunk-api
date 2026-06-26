<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\FoodTruck;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    /**
     * GET /api/company/food-trucks/{foodTruck}/menus/{menu}/items
     */
    public function index(FoodTruck $foodTruck, Menu $menu): JsonResponse
    {
        $items = $menu->items()->orderBy('sort_order')->get();

        return response()->json(['data' => MenuItemResource::collection($items)]);
    }

    /**
     * POST /api/company/food-trucks/{foodTruck}/menus/{menu}/items
     */
    public function store(StoreMenuItemRequest $request, FoodTruck $foodTruck, Menu $menu): JsonResponse
    {
        $item = $menu->items()->create($request->validated());

        return response()->json([
            'data' => new MenuItemResource($item),
            'message' => __('messages.menu_item.created'),
        ], 201);
    }

    /**
     * GET /api/company/food-trucks/{foodTruck}/menus/{menu}/items/{item}
     */
    public function show(FoodTruck $foodTruck, Menu $menu, MenuItem $item): JsonResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        return response()->json(['data' => new MenuItemResource($item)]);
    }

    /**
     * PUT/PATCH /api/company/food-trucks/{foodTruck}/menus/{menu}/items/{item}
     */
    public function update(UpdateMenuItemRequest $request, FoodTruck $foodTruck, Menu $menu, MenuItem $item): JsonResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        $item->update($request->validated());

        return response()->json([
            'data' => new MenuItemResource($item),
            'message' => __('messages.menu_item.updated'),
        ]);
    }

    /**
     * DELETE /api/company/food-trucks/{foodTruck}/menus/{menu}/items/{item}
     */
    public function destroy(FoodTruck $foodTruck, Menu $menu, MenuItem $item): JsonResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        $item->delete();

        return response()->json(['message' => __('messages.menu_item.deleted')]);
    }

    private function ensureItemBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_if($item->menu_id !== $menu->id, 404, __('messages.menu_item.not_found'));
    }
}
