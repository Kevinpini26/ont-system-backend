<?php

namespace Modules\Stagiaires\Support;

use Illuminate\Support\Facades\DB;
use Modules\Stagiaires\Contracts\SequenceGenerator;

class DatabaseSequenceGenerator implements SequenceGenerator
{
    public function suivant(string $type, int $annee): int
    {
        // Upsert atomique (INSERT ... ON CONFLICT) : évite toute condition de
        // course entre deux clôtures de stage concurrentes.
        $ligne = DB::selectOne(
            <<<'SQL'
                insert into stagiaire_numero_compteurs (type, annee, dernier_compteur)
                values (?, ?, 1)
                on conflict (type, annee)
                do update set dernier_compteur = stagiaire_numero_compteurs.dernier_compteur + 1
                returning dernier_compteur
            SQL,
            [$type, $annee],
        );

        return (int) $ligne->dernier_compteur;
    }
}
