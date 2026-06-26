<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Company
 */
class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'logo_url' => $this->logo_url,
            'status' => $this->status,
            'approved_at' => $this->approved_at,
            'food_trucks_count' => $this->whenCounted('foodTrucks'),
            'active_subscription' => new SubscriptionResource($this->whenLoaded('activeSubscription')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
