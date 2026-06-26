<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodTruckResource;
use App\Models\FoodTruck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformFoodTruckController extends Controller
{
    /**
     * GET /api/platform/food-trucks
     * Vista de SOLO LECTURA: todos los food trucks de todas las empresas.
     * El Global Scope (CompanyScope) ya deja pasar todo para platform-owner.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FoodTruck::query()->with('company')->withCount(['locations', 'menus']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->filled('cuisine_type')) {
            $query->where('cuisine_type', $request->string('cuisine_type'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        $foodTrucks = $query->latest()->paginate($request->integer('per_page', 25));

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
     * GET /api/platform/food-trucks/{foodTruck}
     */
    public function show(FoodTruck $foodTruck): JsonResponse
    {
        $foodTruck->load('company')->loadCount(['locations', 'menus']);

        return response()->json(['data' => new FoodTruckResource($foodTruck)]);
    }
}
