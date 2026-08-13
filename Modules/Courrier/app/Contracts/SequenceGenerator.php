<?php

namespace Modules\Courrier\Contracts;

/**
 * Point d'extension : fournit le prochain numéro de séquence, atomiquement,
 * pour un type de référence et une année donnés (ex: accusés de réception,
 * numéros d'enregistrement).
 */
interface SequenceGenerator
{
    public function suivant(string $type, int $annee): int;
}
