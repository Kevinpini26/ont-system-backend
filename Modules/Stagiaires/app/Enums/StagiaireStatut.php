<?php

namespace Modules\Stagiaires\Enums;

enum StagiaireStatut: string
{
    case DOSSIER_RECU = 'dossier_recu';
    case EN_ATTENTE_AFFECTATION = 'en_attente_affectation';
    case AFFECTE = 'affecte';
    case STAGE_EN_COURS = 'stage_en_cours';
    case EVALUATION_EN_COURS = 'evaluation_en_cours';
    case CLOTURE = 'cloture';

    public function label(): string
    {
        return match ($this) {
            self::DOSSIER_RECU => 'Dossier reçu',
            self::EN_ATTENTE_AFFECTATION => "En attente d'affectation",
            self::AFFECTE => 'Affecté',
            self::STAGE_EN_COURS => 'Stage en cours',
            self::EVALUATION_EN_COURS => 'Évaluation en cours',
            self::CLOTURE => 'Clôturé',
        };
    }
}
