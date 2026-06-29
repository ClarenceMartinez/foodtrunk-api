<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'email', 'phone', 'avatar_url',
        'password', 'status', 'last_login_at', 'fcm_token',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * "Favoritos" y "Follow" son la misma relacion (decision de producto:
     * un solo corazon = seguir). El pivot notifications_enabled controla
     * si el usuario recibe notificaciones de ese food truck sin dejar
     * de seguirlo (HU-05).
     */
    public function favoriteFoodTrucks(): BelongsToMany
    {
        return $this->belongsToMany(FoodTruck::class, 'favorites')
            ->withPivot('notifications_enabled')
            ->withTimestamps();
    }

    /**
     * Preferencias globales de notificaciones (Smart Nearby Alerts).
     * Usar notificationSettings() (con metodo) para obtener-o-crear con
     * defaults; el atributo de Eloquent normal devolveria null si el
     * usuario nunca ha guardado preferencias.
     */
    public function notificationSettingsRelation(): HasOne
    {
        return $this->hasOne(UserNotificationSettings::class);
    }

    /**
     * Devuelve las preferencias del usuario, creandolas con los
     * defaults de la migracion si todavia no existen (evita nulls
     * repartidos por todo el codigo que consume esto).
     */
    public function notificationSettings(): UserNotificationSettings
    {
        return $this->notificationSettingsRelation ?? $this->notificationSettingsRelation()->create([]);
    }

    public function lastLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function isPlatformOwner(): bool
    {
        return $this->hasRole('platform-owner');
    }
}
