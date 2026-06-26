<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodTruckResource;
use App\Http\Resources\PromotionResource;
use App\Models\FoodTruck;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    /**
     * GET /api/discover/food-trucks
     * Directorio PÚBLICO de food trucks. No requiere autenticación.
     * Solo muestra food trucks activos de empresas activas.
     *
     * Usamos withoutGlobalScopes() a propósito: esta ruta debe comportarse
     * igual sin importar si quien la llama está autenticado o no (un
     * consumer autenticado no tiene company_id, así que el scope normal
     * lo rompería). Aquí filtramos manualmente lo que sí debe ser público.
     */
    public function foodTrucks(Request $request): JsonResponse
    {
        $query = FoodTruck::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereHas('company', fn ($q) => $q->where('status', 'active'))
            ->with(['company:id,name', 'locations'])
            ->withCount('locations');

        if ($request->filled('cuisine_type')) {
            $query->where('cuisine_type', $request->string('cuisine_type'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        // Filtro de proximidad simple (lat/lng + radio en millas) usando
        // la fórmula de Haversine sobre la tabla de ubicaciones.
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = $request->float('lat');
            $lng = $request->float('lng');
            $radius = $request->float('radius', 10);

            $query->whereHas('locations', function ($q) use ($lat, $lng, $radius) {
                $q->selectRaw(
                    '*, (3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                )->havingRaw('distance <= ?', [$radius]);
            });
        }

        $foodTrucks = $query->paginate($request->integer('per_page', 20));

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
     * GET /api/discover/food-trucks/nearby
     * Búsqueda de food trucks cercanos a un punto (lat/lng), ordenados
     * por distancia. Pensado para el flujo de la Consumer App: el usuario
     * elige un lugar en el buscador de Google Places (Flutter), la app
     * obtiene lat/lng de ese lugar y los manda aquí.
     *
     * A diferencia de foodTrucks() (que solo FILTRA por radio), este
     * endpoint además CALCULA y devuelve la distancia de cada food truck
     * (la de su ubicación más cercana) y los ordena del más cercano al
     * más lejano.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:200',
            'cuisine_type' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:150',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $radius = $request->float('radius', 10);
        $perPage = $request->integer('per_page', 20);

        // Misma fórmula de Haversine en millas que en foodTrucks(), pero
        // aquí hacemos JOIN directo contra locations para poder traer el
        // valor de distancia (la ubicación más cercana de cada truck) y
        // así poder ordenar por cercanía.
        $haversine = '3959 * acos('
            .'cos(radians(?)) * cos(radians(locations.latitude)) '
            .'* cos(radians(locations.longitude) - radians(?)) '
            .'+ sin(radians(?)) * sin(radians(locations.latitude))'
            .')';

        $query = FoodTruck::withoutGlobalScopes()
            ->select('food_trucks.*')
            ->selectRaw("MIN($haversine) as distance", [$lat, $lng, $lat])
            ->join('locations', 'locations.food_truck_id', '=', 'food_trucks.id')
            ->where('food_trucks.status', 'active')
            ->where('locations.status', 'active')
            ->whereHas('company', fn ($q) => $q->where('status', 'active'))
            ->with(['company:id,name', 'locations'])
            ->withCount('locations')
            ->groupBy('food_trucks.id')
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');

        if ($request->filled('cuisine_type')) {
            $query->where('food_trucks.cuisine_type', $request->string('cuisine_type'));
        }

        if ($request->filled('search')) {
            $query->where('food_trucks.name', 'like', '%'.$request->string('search').'%');
        }

        $foodTrucks = $query->paginate($perPage);

        // Adjuntamos distance_miles a cada item del Resource. La columna
        // "distance" viene del selectRaw de arriba, ya está cargada en
        // el modelo como atributo dinámico (no requiere consulta extra).
        $data = collect($foodTrucks->items())->map(function (FoodTruck $foodTruck) {
            return [
                ...((new FoodTruckResource($foodTruck))->resolve()),
                'distance_miles' => round((float) $foodTruck->distance, 2),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $foodTrucks->currentPage(),
                'last_page' => $foodTrucks->lastPage(),
                'total' => $foodTrucks->total(),
                'center' => ['lat' => $lat, 'lng' => $lng],
                'radius_miles' => $radius,
            ],
        ]);
    }

    /**
     * GET /api/discover/food-trucks/{foodTruck}
     * Detalle público: menús (con platillos), ubicaciones, promociones
     * activas. Si el food truck o su empresa no están activos, 404.
     */
    public function foodTruckDetail(FoodTruck $foodTruck): JsonResponse
    {
        abort_if(
            $foodTruck->status !== 'active' || $foodTruck->company->status !== 'active',
            404
        );

        $foodTruck->load([
            'company:id,name',
            'locations' => fn ($q) => $q->where('status', 'active'),
            'menus' => fn ($q) => $q->where('status', 'active'),
            'menus.items' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order'),
        ]);

        $activePromotions = Promotion::withoutGlobalScopes()
            ->where('food_truck_id', $foodTruck->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return response()->json([
            'data' => [
                ...((new FoodTruckResource($foodTruck))->resolve()),
                'active_promotions' => PromotionResource::collection($activePromotions),
            ],
        ]);
    }

    /**
     * GET /api/discover/promotions
     * Promociones vigentes ahora mismo, de cualquier food truck activo.
     */
    public function promotions(Request $request): JsonResponse
    {
        $query = Promotion::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereHas('company', fn ($q) => $q->where('status', 'active'))
            ->with(['foodTruck:id,name,logo_url,cuisine_type', 'company:id,name']);

        if ($request->filled('food_truck_id')) {
            $query->where('food_truck_id', $request->integer('food_truck_id'));
        }

        $promotions = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => PromotionResource::collection($promotions->items()),
            'meta' => [
                'current_page' => $promotions->currentPage(),
                'last_page' => $promotions->lastPage(),
                'total' => $promotions->total(),
            ],
        ]);
    }
}
