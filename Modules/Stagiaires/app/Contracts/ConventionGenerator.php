<?php

namespace Modules\Stagiaires\Contracts;

use Modules\Stagiaires\Models\Stagiaire;

/**
 * Point d'extension : génère le PDF de convention de stage à la validation
 * de l'arrivée (direction et dates de stage connues à ce stade).
 */
interface ConventionGenerator
{
    /**
     * @return string Chemin de stockage (disk "local") du PDF généré.
     */
    public function generer(Stagiaire $stagiaire): string;
}
