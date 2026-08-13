<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class QuotaAffectationTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_laffectation_est_bloquee_quand_la_capacite_maximale_est_atteinte(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create(['actif' => true, 'capacite_max' => 1]);

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $candidat = Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);

        $response = $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$candidat->id}/affecter", ['direction_id' => $direction->id])
            ->assertStatus(422);

        $response->assertJson(['quota_atteint' => true]);
        $this->assertSame(StagiaireStatut::EN_ATTENTE_AFFECTATION->value, $candidat->fresh()->statut->value);
    }

    public function test_la_derogation_avec_justification_permet_de_depasser_le_quota_et_est_journalisee(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create(['actif' => true, 'capacite_max' => 1]);

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $candidat = Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$candidat->id}/affecter", [
                'direction_id' => $direction->id,
                'forcer' => true,
                'justification' => 'Stagiaire déjà en poste, départ imminent d\'un autre.',
            ])
            ->assertOk()
            ->assertJsonPath('data.statut', StagiaireStatut::AFFECTE->value)
            ->assertJsonPath('data.affecte_hors_quota', true);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stagiaire.affectation_hors_quota',
            'auditable_id' => $candidat->id,
        ]);
    }

    public function test_la_derogation_sans_justification_est_rejetee(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create(['actif' => true, 'capacite_max' => 1]);

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $candidat = Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$candidat->id}/affecter", [
                'direction_id' => $direction->id,
                'forcer' => true,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('justification');
    }

    public function test_aucune_limite_de_quota_quand_capacite_max_est_nulle(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $direction = Direction::factory()->create(['actif' => true, 'capacite_max' => null]);

        Stagiaire::factory()->count(5)->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
        ]);

        $candidat = Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$candidat->id}/affecter", ['direction_id' => $direction->id])
            ->assertOk()
            ->assertJsonPath('data.affecte_hors_quota', false);
    }
}
