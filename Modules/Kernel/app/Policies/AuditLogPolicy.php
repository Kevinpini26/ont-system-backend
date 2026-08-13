<?php

namespace Modules\Kernel\Policies;

use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Models\User;

/**
 * Consultation du journal d'audit : réservée à l'administrateur, seul rôle
 * habilité à instruire un litige a posteriori.
 */
class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMINISTRATEUR;
    }
}
