<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Promotion
 */
class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->whenLoaded('company', fn () => $this->company?->name),
            'food_truck_id' => $this->food_truck_id,
            'food_truck' => $this->whenLoaded('foodTruck', fn () => [
                'id' => $this->foodTruck->id,
                'name' => $this->foodTruck->name,
            ]),
            'title' => $this->title,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'is_currently_active' => $this->status === 'active'
                && now()->toDateString() >= $this->start_date->toDateString()
                && now()->toDateString() <= $this->end_date->toDateString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
