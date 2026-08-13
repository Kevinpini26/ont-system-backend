<?php

namespace Modules\Kernel\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Modules\Kernel\Contracts\DirectionScopeBypassResolver;

/**
 * Filtre automatiquement les enregistrements (courriers, stagiaires) selon
 * la direction de rattachement de l'utilisateur connecté, sauf lorsque le
 * DirectionScopeBypassResolver considère que l'utilisateur voit tout.
 */
class DirectionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (app(DirectionScopeBypassResolver::class)->bypasses($user)) {
            return;
        }

        $builder->where($model->qualifyColumn('direction_id'), $user->direction_id);
    }
}
