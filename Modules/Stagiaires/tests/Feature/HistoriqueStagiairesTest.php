<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class HistoriqueStagiairesTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_larchive_est_filtrable_par_annee_direction_etablissement_et_nom(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create();
        $autreDirection = Direction::factory()->create();

        $cible = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::CLOTURE,
            'direction_id' => $direction->id,
            'nom' => 'Alice Tshisekedi',
            'etablissement_origine' => 'Université Protestante au Congo',
            'cloture_at' => '2024-06-15 10:00:00',
        ]);

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::CLOTURE,
            'direction_id' => $autreDirection->id,
            'nom' => 'Bruno Kalala',
            'etablissement_origine' => 'ISC Kinshasa',
            'cloture_at' => '2023-01-10 10:00:00',
        ]);

        $reponse = $this->actingAs($dfp)->getJson('/api/v1/stagiaires?'.http_build_query([
            'statut' => StagiaireStatut::CLOTURE->value,
            'annee' => 2024,
            'direction_id' => $direction->id,
            'etablissement_origine' => 'protestante',
            'nom' => 'alice',
        ]))->assertOk();

        $ids = collect($reponse->json('data'))->pluck('id');
        $this->assertEquals([$cible->id], $ids->all());
    }

    public function test_larchive_reste_consultable_sans_limite_de_date(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        $ancien = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::CLOTURE,
            'cloture_at' => '2018-03-01 10:00:00',
        ]);

        $reponse = $this->actingAs($dfp)->getJson('/api/v1/stagiaires?'.http_build_query([
            'statut' => StagiaireStatut::CLOTURE->value,
            'annee' => 2018,
        ]))->assertOk();

        $this->assertContains($ancien->id, collect($reponse->json('data'))->pluck('id')->all());
    }
}
