<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodTruckRequest;
use App\Http\Requests\UpdateFoodTruckRequest;
use App\Http\Resources\FoodTruckResource;
use App\Models\FoodTruck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodTruckController extends Controller
{
    /**
     * GET /api/company/food-trucks
     * Gracias al trait BelongsToCompany, esto SOLO devuelve los food trucks
     * de la empresa del usuario autenticado (o todos, si es platform-owner).
     */
    public function index(Request $request): JsonResponse
    {
        $query = FoodTruck::query()->withCount(['locations', 'menus']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        $foodTrucks = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => FoodTruckResource::collection($foodTrucks->items()),
            'meta' => [
                'current_page' => $foodTrucks->currentPage(),
                'last_page' => $foodTrucks->lastPage(),
                'total' => $foodTrucks->total(),
            ],
        ]);
    }

    /**
     * POST /api/company/food-trucks
     * El company_id se asigna automáticamente vía BelongsToCompany::creating().
     */
    public function store(StoreFoodTruckRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name'], $request->user()->company_id);
        $data['status'] = $data['status'] ?? 'pending';

        $foodTruck = FoodTruck::create($data);

        return response()->json([
            'data' => new FoodTruckResource($foodTruck),
            'message' => __('messages.food_truck.created'),
        ], 201);
    }

    /**
     * GET /api/company/food-trucks/{foodTruck}
     * Si el food truck pertenece a otra empresa, el Global Scope ya lo
     * excluye de la consulta y Laravel devuelve 404 automáticamente.
     */
    public function show(FoodTruck $foodTruck): JsonResponse
    {
        $foodTruck->loadCount(['locations', 'menus'])->load(['locations', 'menus']);

        return response()->json(['data' => new FoodTruckResource($foodTruck)]);
    }

    /**
     * PUT/PATCH /api/company/food-trucks/{foodTruck}
     */
    public function update(UpdateFoodTruckRequest $request, FoodTruck $foodTruck): JsonResponse
    {
        $foodTruck->update($request->validated());

        return response()->json([
            'data' => new FoodTruckResource($foodTruck),
            'message' => __('messages.food_truck.updated'),
        ]);
    }

    /**
     * DELETE /api/company/food-trucks/{foodTruck}
     */
    public function destroy(FoodTruck $foodTruck): JsonResponse
    {
        $foodTruck->delete();

        return response()->json(['message' => __('messages.food_truck.deleted')]);
    }

    private function generateUniqueSlug(string $name, ?int $companyId): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (FoodTruck::withoutGlobalScopes()->where('company_id', $companyId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
