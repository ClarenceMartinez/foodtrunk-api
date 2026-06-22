<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aplica multi-tenancy a nivel de modelo.
 *
 * Cualquier modelo que use este trait automáticamente:
 *  - Filtra sus resultados por la company_id del usuario autenticado.
 *  - Excepción: el Platform Owner (rol "platform-owner") ve todo, sin filtro.
 *  - Asigna automáticamente el company_id al crear un registro nuevo.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id) && ! auth()->user()->hasRole('platform-owner')) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
