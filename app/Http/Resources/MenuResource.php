<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Menu
 */
class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'food_truck_id' => $this->food_truck_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'items_count' => $this->whenCounted('items'),
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
