<?php

namespace Modules\Public\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Courrier\Enums\AvisDg;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Models\Courrier;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;
use Tests\TestCase;

class VerificationDossierPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comme pour une demande de stage, le statut interne du circuit (huit
     * étapes : Protocole, avis DG, projet de réponse, relecture...) ne doit
     * jamais fuiter vers l'expéditeur d'un courrier externe — seul un
     * statut simplifié à deux valeurs ("En cours de traitement" / "Traité")
     * est exposé.
     */
    public function test_un_candidat_peut_consulter_le_statut_dune_correspondance_generale_sans_authentification(): void
    {
        $courrier = Courrier::factory()->create([
            'numero_accuse_reception' => 'AR-2026-000042',
            'statut' => CourrierStatut::AU_PROTOCOLE,
        ]);

        $response = $this->getJson('/api/v1/public/dossiers/AR-2026-000042');

        $response->assertOk()
            ->assertJsonPath('data.numero_accuse_reception', 'AR-2026-000042')
            ->assertJsonPath('data.statut_simplifie', 'En cours de traitement')
            ->assertJsonMissing(['statut'])
            ->assertJsonMissing(['statut_label'])
            ->assertJsonMissing(['avis_dg_commentaire'])
            ->assertJsonMissing(['note_technique']);
    }

    public function test_une_correspondance_generale_enregistree_expose_le_statut_simplifie_traite(): void
    {
        $courrier = Courrier::factory()->create([
            'numero_accuse_reception' => 'AR-2026-000043',
            'statut' => CourrierStatut::ENREGISTRE,
        ]);

        $this->getJson('/api/v1/public/dossiers/AR-2026-000043')
            ->assertOk()
            ->assertJsonPath('data.statut_simplifie', 'Traité');
    }

    /**
     * Pour une demande de stage, le statut interne du circuit (huit
     * étapes) ne doit jamais fuiter vers le candidat : seul un statut
     * simplifié à trois valeurs est exposé.
     */
    public function test_une_demande_de_stage_expose_un_statut_simplifie_en_cours_dexamen(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'numero_accuse_reception' => 'AR-2026-000050',
            'statut' => CourrierStatut::EN_CIRCUIT_HIERARCHIQUE,
        ]);

        $this->getJson('/api/v1/public/dossiers/AR-2026-000050')
            ->assertOk()
            ->assertJsonPath('data.statut_simplifie', "En cours d'examen")
            ->assertJsonMissing(['statut'])
            ->assertJsonMissing(['statut_label']);
    }

    public function test_une_demande_de_stage_avec_avis_favorable_expose_le_bon_statut_simplifie(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'numero_accuse_reception' => 'AR-2026-000051',
            'statut' => CourrierStatut::PROJET_REPONSE_EN_COURS,
            'avis_dg' => AvisDg::FAVORABLE,
        ]);

        $this->getJson('/api/v1/public/dossiers/AR-2026-000051')
            ->assertOk()
            ->assertJsonPath('data.statut_simplifie', 'Favorable, transmis au service des stages');
    }

    public function test_une_demande_de_stage_avec_avis_defavorable_expose_le_bon_statut_simplifie(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'numero_accuse_reception' => 'AR-2026-000052',
            'statut' => CourrierStatut::PROJET_REPONSE_EN_COURS,
            'avis_dg' => AvisDg::DEFAVORABLE,
        ]);

        $this->getJson('/api/v1/public/dossiers/AR-2026-000052')
            ->assertOk()
            ->assertJsonPath('data.statut_simplifie', 'Non retenu');
    }

    /**
     * Même une fois la fiche stagiaire créée (stage en cours, etc.), le
     * détail interne n'est pas exposé par ce canal : le statut simplifié
     * "favorable" suffit, le déroulement du stage n'a pas à y être suivi.
     */
    public function test_le_detail_de_la_fiche_stagiaire_nest_jamais_expose_publiquement(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'numero_accuse_reception' => 'AR-2026-000099',
            'avis_dg' => AvisDg::FAVORABLE,
        ]);

        Stagiaire::factory()->create([
            'courrier_id' => $courrier->id,
            'statut' => StagiaireStatut::STAGE_EN_COURS,
        ]);

        $this->getJson('/api/v1/public/dossiers/AR-2026-000099')
            ->assertOk()
            ->assertJsonPath('data.stagiaire', null)
            ->assertJsonMissing(['statut' => StagiaireStatut::STAGE_EN_COURS->value]);
    }

    public function test_un_numero_inconnu_renvoie_404(): void
    {
        $this->getJson('/api/v1/public/dossiers/AR-2026-999999')
            ->assertStatus(404);
    }

    public function test_lendpoint_ne_requiert_aucune_authentification(): void
    {
        Courrier::factory()->create(['numero_accuse_reception' => 'AR-2026-000001']);

        // Aucun actingAs / token : l'appel doit tout de même aboutir.
        $this->getJson('/api/v1/public/dossiers/AR-2026-000001')->assertOk();
    }
}
