<?php

namespace Modules\Courrier\Enums;

enum CourrierType: string
{
    case DEMANDE_STAGE = 'demande_stage';
    case CORRESPONDANCE_GENERALE = 'correspondance_generale';

    public function label(): string
    {
        return match ($this) {
            self::DEMANDE_STAGE => 'Demande de stage',
            self::CORRESPONDANCE_GENERALE => 'Correspondance générale',
        };
    }
}
