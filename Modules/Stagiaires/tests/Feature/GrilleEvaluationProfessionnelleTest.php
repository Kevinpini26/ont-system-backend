<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\DocumentType;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * Même mécanique que GrilleEvaluationTest (période d'évaluation,
 * confidentialité, moyenne), mais avec la grille officielle du stage
 * professionnel (10 rubriques/100, pas de justification par section) —
 * jamais mélangée avec celle du stage académique (voir
 * StagiaireTypeStage::classeGrille()).
 */
class GrilleEvaluationProfessionnelleTest extends StagiaireTestCase
{
    use RefreshDatabase;

    private function grille(float $facteur): array
    {
        $valeur = 10 * $facteur;

        return [
            'aspects_intellectuels' => [
                'connaissance_metier' => $valeur,
                'esprit_initiative_responsabilite' => $valeur,
                'capacite_ecoute_communication' => $valeur,
            ],
            'aspects_humains' => [
                'assiduite_discipline' => $valeur,
                'relation_interpersonnelle' => $valeur,
                'ponctualite_regularite' => $valeur,
                'presentation_contacts' => $valeur,
            ],
            'aspects_professionnels' => [
                'efficacite_rendement' => $valeur,
                'capacite_innovation' => $valeur,
                'maitrise_langue' => $valeur,
            ],
        ];
    }

    private function stagiaireEnEvaluation(Direction $direction, bool $periodeOuverte = true): Stagiaire
    {
        return Stagiaire::factory()->professionnel()->create([
            'statut' => StagiaireStatut::EVALUATION_EN_COURS,
            'direction_id' => $direction->id,
            'periode_evaluation_ouverte_at' => $periodeOuverte ? now() : null,
        ]);
    }

    public function test_la_direction_ne_peut_pas_evaluer_tant_que_la_dfp_na_pas_ouvert_la_periode(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction, periodeOuverte: false);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $this->grille(0.8)])
            ->assertStatus(403);
    }

    public function test_le_total_est_toujours_recalcule_cote_serveur(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction);

        // Un champ hors barème (15 > max 10) doit être rejeté, pas juste ignoré.
        $grilleInvalide = $this->grille(0.8);
        $grilleInvalide['aspects_intellectuels']['connaissance_metier'] = 15;

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $grilleInvalide])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['grille.aspects_intellectuels.connaissance_metier']);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $this->grille(0.8)])
            ->assertOk();

        $this->assertEquals(80.0, $stagiaire->fresh()->evaluation_direction_total);
    }

    public function test_la_grille_academique_est_rejetee_pour_un_stagiaire_professionnel(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction);

        // La grille académique (3 sections, champs différents) n'a aucun
        // sens pour un stagiaire professionnel : les 10 rubriques
        // attendues sont toutes absentes, donc rejetées comme requises.
        $grilleAcademique = [
            'aptitudes_professionnelles' => ['connaissance_metier' => 8, 'esprit_initiative' => 8, 'sens_responsabilite' => 8, 'soin_proprete' => 8, 'rendement' => 8],
            'relations_humaines' => ['esprit_equipe' => 8, 'communication' => 8, 'relations_sociales' => 8],
            'presentation' => ['discipline' => 4, 'ponctualite' => 4, 'regularite' => 4, 'tenue' => 4],
        ];

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $grilleAcademique])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['grille.aspects_intellectuels.connaissance_metier']);
    }

    public function test_confidentialite_apres_soumission_par_la_direction(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $this->grille(0.8)])
            ->assertOk()
            ->assertJsonMissingPath('data.evaluation');

        $reponse = $this->actingAs($responsable)
            ->getJson("/api/v1/stagiaires/{$stagiaire->id}")
            ->assertOk();

        $reponse->assertJsonMissingPath('data.evaluation');
        $reponse->assertJsonPath('data.evaluation_direction_soumise', true);
        $this->assertStringNotContainsString('"total":80', $reponse->getContent());
    }

    public function test_la_dfp_voit_le_detail_complet_et_la_moyenne_une_fois_les_deux_soumises(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $dfp = User::factory()->agentDfp()->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $this->grille(0.8)])
            ->assertOk();

        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-dfp", ['grille' => $this->grille(0.6)])
            ->assertOk()
            ->assertJsonPath('data.statut', StagiaireStatut::CLOTURE->value)
            ->assertJsonPath('data.evaluation.direction.total', 80)
            ->assertJsonPath('data.evaluation.dfp.total', 60)
            ->assertJsonPath('data.evaluation.note_finale', 70);
    }

    public function test_la_direction_ne_peut_pas_telecharger_lattestation_meme_apres_cloture(): void
    {
        Storage::fake('local');

        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();
        $dfp = User::factory()->agentDfp()->create();
        $stagiaire = $this->stagiaireEnEvaluation($direction);

        $this->actingAs($responsable)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-direction", ['grille' => $this->grille(0.8)])
            ->assertOk();
        $this->actingAs($dfp)
            ->postJson("/api/v1/stagiaires/{$stagiaire->id}/evaluer-dfp", ['grille' => $this->grille(0.6)])
            ->assertOk();

        $attestation = $stagiaire->fresh()->documents()->where('type', DocumentType::ATTESTATION_STAGE)->firstOrFail();

        $this->actingAs($responsable)
            ->getJson("/api/v1/stagiaires/{$stagiaire->id}/documents/{$attestation->id}/telecharger")
            ->assertStatus(403);

        $this->actingAs($dfp)
            ->get("/api/v1/stagiaires/{$stagiaire->id}/documents/{$attestation->id}/telecharger")
            ->assertOk();
    }
}
