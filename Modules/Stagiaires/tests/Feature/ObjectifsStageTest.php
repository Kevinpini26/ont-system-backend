<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class ObjectifsStageTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_la_direction_daccueil_peut_definir_entre_deux_et_cinq_objectifs(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/objectifs", [
                'objectifs' => ['Rédiger un rapport mensuel', 'Assister le service communication'],
            ])
            ->assertOk()
            ->assertJsonPath('data.objectifs', ['Rédiger un rapport mensuel', 'Assister le service communication']);
    }

    public function test_un_seul_objectif_est_refuse(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/objectifs", ['objectifs' => ['Un seul objectif']])
            ->assertStatus(422);
    }

    public function test_une_autre_direction_ne_peut_pas_definir_les_objectifs(): void
    {
        $direction = Direction::factory()->create();
        $autreDirection = Direction::factory()->create();
        $autreResponsable = User::factory()->responsableDirection($autreDirection)->create();

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $this->actingAs($autreResponsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/objectifs", ['objectifs' => ['A', 'B']])
            ->assertStatus(404);
    }
}
