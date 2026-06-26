<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformLocationController extends Controller
{
    /**
     * GET /api/platform/locations
     * Vista de SOLO LECTURA: todas las ubicaciones de todos los food trucks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Location::query()->with('foodTruck.company');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->filled('food_truck_id')) {
            $query->where('food_truck_id', $request->integer('food_truck_id'));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->string('state'));
        }

        if ($request->filled('search')) {
            $query->where('address', 'like', '%'.$request->string('search').'%');
        }

        $locations = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => LocationResource::collection($locations->items()),
            'meta' => [
                'current_page' => $locations->currentPage(),
                'last_page' => $locations->lastPage(),
                'total' => $locations->total(),
            ],
        ]);
    }
}
