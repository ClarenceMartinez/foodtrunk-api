<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Subscription
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->whenLoaded('company', fn () => $this->company?->name),
            'status' => $this->status,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
        ];
    }
}
