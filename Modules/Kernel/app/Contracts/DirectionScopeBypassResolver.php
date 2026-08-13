<?php

namespace Modules\Kernel\Contracts;

use Modules\Kernel\Models\User;

/**
 * Point d'extension : décide si un utilisateur donné doit voir les
 * enregistrements de toutes les directions plutôt que ceux de sa
 * seule direction de rattachement.
 */
interface DirectionScopeBypassResolver
{
    public function bypasses(User $user): bool;
}
