<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        // El Platform Owner ve todos los registros, de todas las empresas.
        // El Consumer tampoco pertenece a ninguna empresa — sus consultas
        // van por endpoints públicos de /discover, que ya ignoran este
        // scope explícitamente, pero lo eximimos aquí también por seguridad.
        if ($user->hasRole('platform-owner') || $user->hasRole('consumer')) {
            return;
        }

        $builder->where($model->getTable().'.company_id', $user->company_id);
    }
}
