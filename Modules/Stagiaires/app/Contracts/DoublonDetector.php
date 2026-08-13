<?php

namespace Modules\Stagiaires\Contracts;

use Modules\Stagiaires\Models\Stagiaire;

/**
 * Point d'extension : recherche, parmi les stagiaires existants, un dossier
 * proche (nom + établissement d'origine) du candidat donné — comparaison
 * approximative, pas une égalité stricte. Ne bloque jamais la création : la
 * décision finale revient à la DFP.
 */
interface DoublonDetector
{
    public function trouverDoublon(Stagiaire $candidat): ?Stagiaire;
}
