<?php

namespace Modules\Stagiaires\Contracts;

/**
 * Point d'extension : critère de calcul de la note finale à partir des deux
 * évaluations (direction d'accueil + DFP). Par défaut, une simple moyenne.
 */
interface CalculateurNoteFinale
{
    public function calculer(float $noteDirection, float $noteDfp): float;
}
