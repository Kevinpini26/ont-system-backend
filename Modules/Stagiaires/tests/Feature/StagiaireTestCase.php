<?php

namespace Modules\Stagiaires\Tests\Feature;

use Modules\Courrier\Models\Courrier;
use Tests\TestCase;

abstract class StagiaireTestCase extends TestCase
{
    /**
     * Un courrier créé directement via factory à un statut intermédiaire
     * (raccourci de test, sans passer par le circuit réel) n'a aucun
     * bordereau : sans cet appel, il apparaît "en transit" indéfiniment et
     * bloque toute action — voir Courrier::enTransit().
     */
    protected function marquerDecharge(Courrier $courrier): void
    {
        $courrier->transitions()->create([
            'statut' => $courrier->statut,
            'accuse_reception_at' => now(),
            'created_at' => now(),
        ]);
    }
}
