<?php

namespace Modules\Stagiaires\Enums;

use Modules\Stagiaires\Support\GrilleEvaluation;
use Modules\Stagiaires\Support\GrilleEvaluationProfessionnelle;

enum StagiaireTypeStage: string
{
    case ACADEMIQUE = 'academique';
    case PROFESSIONNEL = 'professionnel';

    public function label(): string
    {
        return match ($this) {
            self::ACADEMIQUE => 'Stage académique',
            self::PROFESSIONNEL => 'Stage professionnel',
        };
    }

    /**
     * Grille d'évaluation officielle correspondante — voir
     * Modules\Stagiaires\Support\GrilleEvaluation (académique) et
     * GrilleEvaluationProfessionnelle (professionnel), jamais mélangées.
     *
     * @return class-string
     */
    public function classeGrille(): string
    {
        return match ($this) {
            self::ACADEMIQUE => GrilleEvaluation::class,
            self::PROFESSIONNEL => GrilleEvaluationProfessionnelle::class,
        };
    }
}
