<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * GET /api/company/subscription
     * Muestra la suscripción activa actual de la empresa (si tiene).
     */
    public function current(Request $request): JsonResponse
    {
        $subscription = Subscription::with('plan')
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $subscription) {
            return response()->json([
                'data' => null,
                'message' => __('messages.subscription.no_subscription'),
            ]);
        }

        return response()->json(['data' => new SubscriptionResource($subscription)]);
    }

    /**
     * GET /api/company/subscriptions
     * Historial completo de suscripciones de la empresa.
     */
    public function history(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with('plan')->latest()->get();

        return response()->json(['data' => SubscriptionResource::collection($subscriptions)]);
    }

    /**
     * POST /api/company/subscribe
     * Suscribe (o cambia de plan) a la empresa autenticada.
     * NOTA: esta fase NO procesa cobro real con Stripe — solo activa el
     * plan. La integración de pago se agrega en una fase posterior.
     */
    public function subscribe(StoreSubscriptionRequest $request): JsonResponse
    {
        $plan = Plan::findOrFail($request->validated('plan_id'));

        abort_if($plan->status !== 'active', 422, __('messages.plan.not_available'));

        // Cancela cualquier suscripción activa anterior antes de crear la nueva.
        Subscription::where('company_id', $request->user()->company_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $startsAt = now();
        $endsAt = $plan->billing_cycle === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $subscription = Subscription::create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return response()->json([
            'data' => new SubscriptionResource($subscription->load('plan')),
            'message' => __('messages.subscription.activated', ['plan' => $plan->name]),
        ], 201);
    }

    /**
     * POST /api/company/subscription/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = Subscription::where('company_id', $request->user()->company_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        abort_if(! $subscription, 404, __('messages.subscription.no_active_subscription'));

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'data' => new SubscriptionResource($subscription->load('plan')),
            'message' => __('messages.subscription.cancelled'),
        ]);
    }
}
