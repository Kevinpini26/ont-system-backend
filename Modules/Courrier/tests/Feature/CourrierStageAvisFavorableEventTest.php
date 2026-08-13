<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Enums\CourrierType;
use Modules\Courrier\Events\CourrierStageAvisFavorable;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;

class CourrierStageAvisFavorableEventTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_lavis_favorable_dune_demande_de_stage_declenche_levenement(): void
    {
        Event::fake([CourrierStageAvisFavorable::class]);

        $direction = Direction::factory()->create();
        $dg = $this->agent(Poste::DG, $direction);

        $courrier = Courrier::factory()->demandeStage()->create([
            'statut' => CourrierStatut::EN_ATTENTE_AVIS_DG,
        ]);
        $this->marquerDecharge($courrier);

        $this->actingAs($dg)
            ->postJson("/api/v1/courriers/{$courrier->id}/rendre-avis", ['avis_dg' => 'favorable'])
            ->assertOk();

        Event::assertDispatched(CourrierStageAvisFavorable::class, function (CourrierStageAvisFavorable $event) use ($courrier) {
            return $event->courrier->is($courrier)
                && $event->candidatNom() === $courrier->candidat_nom
                && $event->referenceCourrier() === $courrier->numero_accuse_reception;
        });
    }

    public function test_un_avis_defavorable_ne_declenche_pas_levenement(): void
    {
        Event::fake([CourrierStageAvisFavorable::class]);

        $direction = Direction::factory()->create();
        $dg = $this->agent(Poste::DG, $direction);

        $courrier = Courrier::factory()->demandeStage()->create([
            'statut' => CourrierStatut::EN_ATTENTE_AVIS_DG,
        ]);
        $this->marquerDecharge($courrier);

        $this->actingAs($dg)
            ->postJson("/api/v1/courriers/{$courrier->id}/rendre-avis", ['avis_dg' => 'defavorable'])
            ->assertOk();

        Event::assertNotDispatched(CourrierStageAvisFavorable::class);
    }

    public function test_un_avis_favorable_sur_une_correspondance_generale_ne_declenche_pas_levenement(): void
    {
        Event::fake([CourrierStageAvisFavorable::class]);

        $direction = Direction::factory()->create();
        $dg = $this->agent(Poste::DG, $direction);

        $courrier = Courrier::factory()->create([
            'type' => CourrierType::CORRESPONDANCE_GENERALE,
            'statut' => CourrierStatut::EN_ATTENTE_AVIS_DG,
        ]);
        $this->marquerDecharge($courrier);

        $this->actingAs($dg)
            ->postJson("/api/v1/courriers/{$courrier->id}/rendre-avis", ['avis_dg' => 'favorable'])
            ->assertOk();

        Event::assertNotDispatched(CourrierStageAvisFavorable::class);
    }
}
