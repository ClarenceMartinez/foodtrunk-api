<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id', 'food_truck_id', 'name', 'address', 'city', 'state',
        'zip_code', 'latitude', 'longitude', 'schedule', 'status',
    ];

    protected $casts = [
        'schedule' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function foodTruck(): BelongsTo
    {
        return $this->belongsTo(FoodTruck::class);
    }
}
