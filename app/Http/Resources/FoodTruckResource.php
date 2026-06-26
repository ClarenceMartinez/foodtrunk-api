<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\FoodTruck
 */
class FoodTruckResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->whenLoaded('company', fn () => $this->company?->name),
            'name' => $this->name,
            'slug' => $this->slug,
            'cuisine_type' => $this->cuisine_type,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'cover_image_url' => $this->cover_image_url,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'locations_count' => $this->whenCounted('locations'),
            'menus_count' => $this->whenCounted('menus'),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'menus' => MenuResource::collection($this->whenLoaded('menus')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
