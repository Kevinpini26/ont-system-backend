<?php

namespace Modules\Kernel\Support;

use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\User;

/**
 * Disponibilité de la DG pour rendre un avis sur le circuit courrier —
 * activable par l'administrateur ou par la DG elle-même avant un congé.
 * Quand la DG est indisponible, la DGA peut intervenir en intérim (voir
 * CourrierCircuitService::rendreAvisDg()). Un seul état partagé par tous
 * les utilisateurs au poste DG, pas un réglage par utilisateur : dans les
 * faits ce poste n'est occupé que par une seule personne à la fois.
 */
class DgDisponibilite
{
    public static function estDisponible(): bool
    {
        return User::query()->where('poste', Poste::DG->value)->value('dg_disponible') ?? true;
    }

    public static function definir(bool $disponible): void
    {
        User::query()->where('poste', Poste::DG->value)->update(['dg_disponible' => $disponible]);
    }
}
