<?php

namespace Modules\Stagiaires\Contracts;

/**
 * Point d'extension : fournit le prochain numéro de séquence, atomiquement,
 * pour un type de référence et une année donnés (ex: numéros d'attestation).
 */
interface SequenceGenerator
{
    public function suivant(string $type, int $annee): int;
}
