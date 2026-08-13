<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class ReaffectationStagiaireTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_la_dfp_peut_reaffecter_un_stagiaire_en_cours_de_stage(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $ancienneDirection = Direction::factory()->create(['actif' => true]);
        $nouvelleDirection = Direction::factory()->create(['actif' => true]);

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $ancienneDirection->id,
        ]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $nouvelleDirection->id,
                'justification' => "Réorganisation interne suite au départ du responsable d'accueil.",
            ])
            ->assertOk()
            ->assertJsonPath('data.statut', StagiaireStatut::STAGE_EN_COURS->value)
            ->assertJsonPath('data.direction.id', $nouvelleDirection->id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stagiaire.reaffectation',
            'auditable_id' => $stagiaire->id,
        ]);
    }

    public function test_la_reaffectation_sans_justification_est_rejetee(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $ancienneDirection = Direction::factory()->create(['actif' => true]);
        $nouvelleDirection = Direction::factory()->create(['actif' => true]);

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $ancienneDirection->id,
        ]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $nouvelleDirection->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('justification');
    }

    public function test_la_reaffectation_vers_la_meme_direction_est_rejetee(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create(['actif' => true]);

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $direction->id,
                'justification' => 'Sans changement réel.',
            ])
            ->assertStatus(422);
    }

    public function test_la_reaffectation_est_impossible_avant_la_premiere_affectation(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $nouvelleDirection = Direction::factory()->create(['actif' => true]);

        $stagiaire = Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $nouvelleDirection->id,
                'justification' => 'Tentative prématurée.',
            ])
            ->assertStatus(422);
    }

    public function test_le_quota_de_la_nouvelle_direction_sapplique_a_la_reaffectation(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $ancienneDirection = Direction::factory()->create(['actif' => true]);
        $nouvelleDirection = Direction::factory()->create(['actif' => true, 'capacite_max' => 1]);

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $nouvelleDirection->id,
        ]);

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $ancienneDirection->id,
        ]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $nouvelleDirection->id,
                'justification' => 'Tentative malgré quota.',
            ])
            ->assertStatus(422)
            ->assertJson(['quota_atteint' => true]);
    }

    public function test_une_direction_daccueil_ne_peut_pas_reaffecter_un_stagiaire(): void
    {
        $ancienneDirection = Direction::factory()->create(['actif' => true]);
        $nouvelleDirection = Direction::factory()->create(['actif' => true]);
        $responsable = User::factory()->responsableDirection($ancienneDirection)->create();

        $stagiaire = Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $ancienneDirection->id,
        ]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/reaffecter", [
                'direction_id' => $nouvelleDirection->id,
                'justification' => 'Tentative non autorisée.',
            ])
            ->assertForbidden();
    }
}
