<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodTruck extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'slug', 'cuisine_type', 'description',
        'logo_url', 'cover_image_url', 'phone', 'email', 'status',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withPivot('notifications_enabled')
            ->withTimestamps();
    }

    /**
     * Alias semantico de favoritedBy(): se usa cuando hablamos de
     * "seguidores" para notificarlos (promociones, eventos, ubicacion).
     * Es la MISMA relacion/tabla, solo un nombre mas claro segun el
     * contexto de uso.
     */
    public function followers(): BelongsToMany
    {
        return $this->favoritedBy();
    }
}
