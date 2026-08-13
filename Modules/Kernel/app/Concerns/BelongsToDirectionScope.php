<?php

namespace Modules\Kernel\Concerns;

use Modules\Kernel\Scopes\DirectionScope;

/**
 * À utiliser sur tout modèle possédant une colonne direction_id dont la
 * visibilité doit être restreinte à la direction de l'utilisateur connecté.
 */
trait BelongsToDirectionScope
{
    public static function bootBelongsToDirectionScope(): void
    {
        static::addGlobalScope(new DirectionScope);
    }
}
