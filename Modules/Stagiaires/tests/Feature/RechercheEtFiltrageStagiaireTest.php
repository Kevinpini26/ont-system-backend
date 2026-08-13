<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

class RechercheEtFiltrageStagiaireTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_la_recherche_libre_porte_sur_nom_et_reference(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create(['nom' => 'Jean Kabila', 'reference_courrier' => 'AR-2026-000001']);
        Stagiaire::factory()->create(['nom' => 'Autre Personne', 'reference_courrier' => 'AR-2026-000002']);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?recherche=Kabila')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_le_filtre_type_stage_est_applique(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create();
        Stagiaire::factory()->professionnel()->create();

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?type_stage=professionnel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type_stage', 'professionnel');
    }

    public function test_le_filtre_maitre_de_stage_est_applique(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create(['maitre_stage' => 'Mme Kalonji']);
        Stagiaire::factory()->create(['maitre_stage' => 'M. Tshibangu']);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?maitre_stage=Kalonji')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_le_filtre_en_cours_exclut_les_autres_statuts(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create(['statut' => StagiaireStatut::STAGE_EN_COURS]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::EVALUATION_EN_COURS]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::AFFECTE]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::CLOTURE]);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?en_cours=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_le_filtre_a_venir_ne_retient_que_les_stagiaires_affectes(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create(['statut' => StagiaireStatut::AFFECTE]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::STAGE_EN_COURS]);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?a_venir=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statut', StagiaireStatut::AFFECTE->value);
    }

    public function test_le_filtre_en_attente_traitement_couvre_dossier_recu_et_en_attente_affectation(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create(['statut' => StagiaireStatut::DOSSIER_RECU]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::EN_ATTENTE_AFFECTATION]);
        Stagiaire::factory()->create(['statut' => StagiaireStatut::AFFECTE]);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?en_attente_traitement=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_avec_assiduite_expose_la_suggestion_dassiduite_sur_la_liste(): void
    {
        $dfp = User::factory()->agentDfp()->create();
        Stagiaire::factory()->create([
            'statut' => StagiaireStatut::STAGE_EN_COURS,
            'date_debut_stage' => now()->subDays(10),
        ]);

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?en_cours=1&avec_assiduite=1')
            ->assertOk()
            ->assertJsonPath('data.0.assiduite_suggestion.regularite', 0);

        // Sans le paramètre, la relation n'est pas chargée : le champ est
        // absent (JsonResource::when()), pour éviter un N+1 sur les listes
        // par défaut.
        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires?en_cours=1')
            ->assertOk()
            ->assertJsonMissingPath('data.0.assiduite_suggestion');
    }
}
