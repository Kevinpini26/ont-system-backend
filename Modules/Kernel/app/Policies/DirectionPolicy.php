<?php

namespace Modules\Kernel\Policies;

use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;

class DirectionPolicy
{
    /**
     * Toute personne authentifiée peut consulter la liste des directions
     * (formulaires d'envoi de courrier, affectation de stagiaires, etc.).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Direction $direction): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }

    public function update(User $user, Direction $direction): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }

    public function delete(User $user, Direction $direction): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }
}
