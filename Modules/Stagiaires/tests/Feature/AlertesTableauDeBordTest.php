<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * GET /stagiaires/alertes — listes exploitables (pas de simples compteurs)
 * pour les tableaux de bord reconstruits.
 */
class AlertesTableauDeBordTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_le_responsable_de_direction_voit_les_alertes_scopees_a_sa_direction(): void
    {
        $direction = Direction::factory()->create();
        $autreDirection = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $direction->id,
            'periode_evaluation_ouverte_at' => null,
        ]);
        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'direction_id' => $autreDirection->id,
            'periode_evaluation_ouverte_at' => null,
        ]);

        $response = $this->actingAs($responsable)
            ->getJson('/api/v1/stagiaires/alertes')
            ->assertOk();

        $this->assertCount(1, $response->json('evaluation_attente_ouverture'));
        // Items transverses réservés à la DFP/admin — absents, pas juste vides.
        $this->assertArrayNotHasKey('demandes_en_attente', $response->json());
        $this->assertArrayNotHasKey('directions_proches_quota', $response->json());
    }

    public function test_la_dfp_voit_les_demandes_en_attente_depuis_le_seuil(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::DOSSIER_RECU,
            'created_at' => now()->subDays(5),
        ]);
        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::DOSSIER_RECU,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires/alertes?seuil_jours=3')
            ->assertOk();

        $this->assertCount(1, $response->json('demandes_en_attente'));
    }

    public function test_la_dfp_voit_les_evaluations_incompletes_avec_le_cote_manquant(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::EVALUATION_EN_COURS,
            'evaluation_direction_at' => now(),
            'evaluation_dfp_at' => null,
        ]);
        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::EVALUATION_EN_COURS,
            'evaluation_direction_at' => null,
            'evaluation_dfp_at' => now(),
        ]);
        // Complète des deux côtés : ne doit pas apparaître.
        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::EVALUATION_EN_COURS,
            'evaluation_direction_at' => now(),
            'evaluation_dfp_at' => now(),
        ]);

        $response = $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires/alertes')
            ->assertOk();

        $incompletes = collect($response->json('evaluations_incompletes'));
        $this->assertCount(2, $incompletes);
        $this->assertEqualsCanonicalizing(['dfp', 'direction'], $incompletes->pluck('manque')->all());
    }

    public function test_la_dfp_voit_les_directions_proches_du_quota(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        $directionProche = Direction::factory()->create(['capacite_max' => 5]);
        $directionLoin = Direction::factory()->create(['capacite_max' => 10]);

        Stagiaire::factory()->count(4)->create(['statut' => StagiaireStatut::STAGE_EN_COURS, 'direction_id' => $directionProche->id]);
        Stagiaire::factory()->count(1)->create(['statut' => StagiaireStatut::STAGE_EN_COURS, 'direction_id' => $directionLoin->id]);

        $response = $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires/alertes')
            ->assertOk();

        $proches = collect($response->json('directions_proches_quota'));
        $this->assertCount(1, $proches);
        $this->assertSame($directionProche->id, $proches->first()['direction_id']);
    }
}
