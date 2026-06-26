<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformSubscriptionController extends Controller
{
    /**
     * GET /api/platform/subscriptions
     * Vista de SOLO LECTURA: todas las suscripciones de todas las empresas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::query()->with(['plan', 'company']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->integer('plan_id'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        $subscriptions = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => SubscriptionResource::collection($subscriptions->items()),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }
}
