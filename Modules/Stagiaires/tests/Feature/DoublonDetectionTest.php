<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Events\CourrierStageAvisFavorable;
use Modules\Courrier\Models\Courrier;
use Modules\Stagiaires\Models\Stagiaire;

class DoublonDetectionTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_un_dossier_proche_dun_dossier_existant_est_signale_sans_bloquer_la_creation(): void
    {
        $existant = Stagiaire::factory()->create([
            'nom' => 'Jean-Pierre Mukendi',
            'etablissement_origine' => 'Université de Kinshasa',
        ]);

        $courrier = Courrier::factory()->demandeStage()->create([
            'statut' => CourrierStatut::EN_ATTENTE_AVIS_DG,
            'candidat_nom' => 'Jean Pierre Mukendi',
            'candidat_etablissement' => 'Université de Kinshasa',
        ]);

        event(new CourrierStageAvisFavorable($courrier));

        $nouveau = Stagiaire::query()->where('courrier_id', $courrier->id)->first();

        $this->assertNotNull($nouveau);
        $this->assertTrue($nouveau->doublon_suspecte);
        $this->assertSame($existant->id, $nouveau->doublon_stagiaire_id);
    }

    public function test_un_dossier_clairement_distinct_nest_pas_signale(): void
    {
        Stagiaire::factory()->create([
            'nom' => 'Jean-Pierre Mukendi',
            'etablissement_origine' => 'Université de Kinshasa',
        ]);

        $courrier = Courrier::factory()->demandeStage()->create([
            'statut' => CourrierStatut::EN_ATTENTE_AVIS_DG,
            'candidat_nom' => 'Sylvie Kabasele',
            'candidat_etablissement' => 'Institut Supérieur de Commerce',
        ]);

        event(new CourrierStageAvisFavorable($courrier));

        $nouveau = Stagiaire::query()->where('courrier_id', $courrier->id)->first();

        $this->assertNotNull($nouveau);
        $this->assertFalse($nouveau->doublon_suspecte);
        $this->assertNull($nouveau->doublon_stagiaire_id);
    }
}
