<?php

namespace App\Http\Controllers\Api;

use App\Actions\NotifyFoodTruckFollowers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Models\FoodTruck;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * GET /api/company/promotions
     * Lista las promociones de la empresa (todas, o filtradas por food truck).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::query()->with('foodTruck');

        if ($request->filled('food_truck_id')) {
            $query->where('food_truck_id', $request->integer('food_truck_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $promotions = $query->latest()->get();

        return response()->json(['data' => PromotionResource::collection($promotions)]);
    }

    /**
     * POST /api/company/promotions
     */
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->ensureFoodTruckBelongsToCompany($data['food_truck_id'] ?? null, $request->user()->company_id);

        $data['status'] = $data['status'] ?? 'scheduled';

        $promotion = Promotion::create($data)->load('foodTruck');

        // HU-06 + Smart Nearby Alerts: notificar a los seguidores.
        // Pasamos $promotion para habilitar el Escenario B (alerta
        // inteligente a quien apago notificaciones pero esta cerca).
        // Solo al CREAR (no en update()) para no generar ruido por cada
        // edicion menor de una promocion ya existente.
        if ($promotion->foodTruck) {
            app(NotifyFoodTruckFollowers::class)->execute(
                $promotion->foodTruck,
                'promotion_created',
                "New promotion from {$promotion->foodTruck->name}",
                $promotion->title,
                ['promotion_id' => $promotion->id],
                $promotion,
            );
        }

        return response()->json([
            'data' => new PromotionResource($promotion),
            'message' => __('messages.promotion.created'),
        ], 201);
    }

    /**
     * GET /api/company/promotions/{promotion}
     */
    public function show(Promotion $promotion): JsonResponse
    {
        return response()->json(['data' => new PromotionResource($promotion->load('foodTruck'))]);
    }

    /**
     * PUT/PATCH /api/company/promotions/{promotion}
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('food_truck_id', $data)) {
            $this->ensureFoodTruckBelongsToCompany($data['food_truck_id'], $request->user()->company_id);
        }

        $promotion->update($data);

        return response()->json([
            'data' => new PromotionResource($promotion->load('foodTruck')),
            'message' => __('messages.promotion.updated'),
        ]);
    }

    /**
     * DELETE /api/company/promotions/{promotion}
     */
    public function destroy(Promotion $promotion): JsonResponse
    {
        $promotion->delete();

        return response()->json(['message' => __('messages.promotion.deleted')]);
    }

    /**
     * La regla "exists:food_trucks,id" no respeta el scope multi-tenant
     * (consulta la BD directamente). Esta verificación extra evita que una
     * empresa cree una promoción apuntando al food truck de otra empresa.
     */
    private function ensureFoodTruckBelongsToCompany(?int $foodTruckId, ?int $companyId): void
    {
        if (! $foodTruckId) {
            return;
        }

        $belongs = FoodTruck::withoutGlobalScopes()
            ->where('id', $foodTruckId)
            ->where('company_id', $companyId)
            ->exists();

        abort_unless($belongs, 422, __('messages.promotion.food_truck_mismatch'));
    }
}
