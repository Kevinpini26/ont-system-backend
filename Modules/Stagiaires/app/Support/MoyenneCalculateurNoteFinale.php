<?php

namespace Modules\Stagiaires\Support;

use Modules\Stagiaires\Contracts\CalculateurNoteFinale;

class MoyenneCalculateurNoteFinale implements CalculateurNoteFinale
{
    public function calculer(float $noteDirection, float $noteDfp): float
    {
        return round(($noteDirection + $noteDfp) / 2, 2);
    }
}
