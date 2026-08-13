<?php

namespace Modules\Kernel\Policies;

use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Models\User;

/**
 * Gestion des comptes et des rôles : réservée à l'administrateur.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }

    public function view(User $user, User $target): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR || $user->is($target);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR && ! $user->is($target);
    }
}
