<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * GET /api/plans
     * Catálogo público de planes activos. Cualquier usuario autenticado
     * (Platform Owner o Company) puede consultarlo para elegir/comparar.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::query();

        // El Platform Owner puede ver también los planes inactivos
        // (para administrarlos); el resto solo ve los activos.
        if (! $request->user()->hasRole('platform-owner')) {
            $query->where('status', 'active');
        }

        return response()->json([
            'data' => PlanResource::collection($query->orderBy('price')->get()),
        ]);
    }

    /**
     * POST /api/platform/plans
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['status'] = $data['status'] ?? 'active';

        $plan = Plan::create($data);

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => __('messages.plan.created'),
        ], 201);
    }

    /**
     * GET /api/plans/{plan}
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => new PlanResource($plan)]);
    }

    /**
     * PUT/PATCH /api/platform/plans/{plan}
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => __('messages.plan.updated'),
        ]);
    }

    /**
     * DELETE /api/platform/plans/{plan}
     */
    public function destroy(Plan $plan): JsonResponse
    {
        abort_if(
            $plan->subscriptions()->exists(),
            422,
            __('messages.plan.has_subscriptions')
        );

        $plan->delete();

        return response()->json(['message' => __('messages.plan.deleted')]);
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Plan::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
