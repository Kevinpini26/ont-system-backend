<?php

namespace Modules\Stagiaires\Support;

use Illuminate\Support\Collection;
use Modules\Kernel\Models\Direction;
use Modules\Stagiaires\Contracts\AffectationRules;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class ActiveDirectionsAffectationRules implements AffectationRules
{
    public function directionsEligibles(): Collection
    {
        return Direction::query()->where('actif', true)->orderBy('code')->get();
    }

    public function estEligible(int $directionId): bool
    {
        return $this->directionsEligibles()->contains('id', $directionId);
    }

    public function estSaturee(int $directionId): bool
    {
        $capaciteMax = Direction::query()->whereKey($directionId)->value('capacite_max');

        if ($capaciteMax === null) {
            return false;
        }

        // withoutGlobalScopes : ce comptage doit refléter l'occupation
        // réelle de la direction, indépendamment du périmètre de visibilité
        // de l'utilisateur qui déclenche l'affectation.
        $occupation = Stagiaire::query()
            ->withoutGlobalScopes()
            ->where('direction_id', $directionId)
            ->whereIn('statut', [StagiaireStatut::AFFECTE, StagiaireStatut::STAGE_EN_COURS])
            ->count();

        return $occupation >= $capaciteMax;
    }
}
