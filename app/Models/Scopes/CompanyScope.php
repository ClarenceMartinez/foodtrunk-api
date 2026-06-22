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

        // El Platform Owner (super administrador) ve todos los registros,
        // de todas las empresas, sin restricción.
        if ($user->hasRole('platform-owner')) {
            return;
        }

        $builder->where($model->getTable().'.company_id', $user->company_id);
    }
}
