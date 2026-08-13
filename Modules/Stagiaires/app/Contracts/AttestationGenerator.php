<?php

namespace Modules\Stagiaires\Contracts;

use Modules\Stagiaires\Models\Stagiaire;

/**
 * Point d'extension : génère le PDF d'attestation de stage à la clôture.
 */
interface AttestationGenerator
{
    /**
     * @return string Chemin de stockage (disk "local") du PDF généré.
     */
    public function generer(Stagiaire $stagiaire): string;
}
