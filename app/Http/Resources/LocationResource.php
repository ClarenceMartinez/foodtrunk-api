<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Location
 */
class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'food_truck_id' => $this->food_truck_id,
            'food_truck_name' => $this->whenLoaded('foodTruck', fn () => $this->foodTruck?->name),
            'company_name' => $this->whenLoaded('foodTruck', fn () => $this->foodTruck?->company?->name),
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'schedule' => $this->schedule,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
