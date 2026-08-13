<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Enums\CourrierType;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;

class EnvoiParDirectionTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_une_direction_peut_envoyer_un_courrier_vers_une_autre_direction(): void
    {
        $directionEmettrice = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($directionEmettrice)->create();

        $response = $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Demande de collaboration',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $directionDestinataire->id,
        ])->assertCreated();

        $response->assertJsonPath('data.statut', CourrierStatut::RECU->value);
        $this->assertDatabaseHas('courriers', [
            'direction_origine_id' => $directionEmettrice->id,
            'direction_destination_id' => $directionDestinataire->id,
        ]);
    }

    public function test_un_courrier_direction_a_direction_suit_le_circuit_court(): void
    {
        $directionEmettrice = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($directionEmettrice)->create();
        $secretariat2 = $this->agent(Poste::SECRETARIAT_2, $directionEmettrice);

        $id = $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Demande de collaboration',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $directionDestinataire->id,
        ])->assertCreated()
            ->assertJsonPath('data.necessite_avis_dg', false)
            ->json('data.id');

        $this->actingAs($secretariat2)->postJson("/api/v1/courriers/{$id}/accuser-reception")->assertOk();

        // Enregistrement centralisé directement depuis "recu", sans passer
        // par le Protocole, la DGA, ni un avis DG.
        $this->actingAs($secretariat2)
            ->postJson("/api/v1/courriers/{$id}/enregistrer", [
                'classification' => 'interne',
                'note_technique' => 'RAS',
            ])
            ->assertOk()
            ->assertJsonPath('data.statut', CourrierStatut::ENREGISTRE->value);

        $this->assertNotNull(Courrier::withoutGlobalScopes()->find($id)->numero_enregistrement);
    }

    public function test_le_protocole_ne_peut_pas_agir_sur_un_courrier_en_circuit_court(): void
    {
        $directionEmettrice = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($directionEmettrice)->create();
        $protocole = $this->agent(Poste::PROTOCOLE, $directionEmettrice);

        $id = $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Demande de collaboration',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $directionDestinataire->id,
        ])->assertCreated()->json('data.id');

        $this->actingAs($protocole)
            ->postJson("/api/v1/courriers/{$id}/transmettre-protocole")
            ->assertStatus(403);
    }

    public function test_un_courrier_dune_direction_vers_la_dg_reste_en_circuit_complet(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Rapport trimestriel',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
        ])->assertCreated()
            ->assertJsonPath('data.necessite_avis_dg', true);
    }

    public function test_une_demande_de_stage_reste_en_circuit_complet_meme_vers_une_direction(): void
    {
        $direction = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Stagiaire recommandé',
            'type' => CourrierType::DEMANDE_STAGE->value,
            'candidat_nom' => 'Jean Test',
            'candidat_contact' => 'jean@example.com',
            'candidat_etablissement' => 'Université Test',
            'periode_souhaitee_debut' => now()->addMonth()->toDateString(),
            'periode_souhaitee_fin' => now()->addMonths(3)->toDateString(),
            'direction_destination_id' => $directionDestinataire->id,
        ])->assertCreated()
            ->assertJsonPath('data.necessite_avis_dg', true);
    }

    public function test_un_courrier_cree_par_la_reception_reste_en_circuit_complet_meme_vers_une_direction(): void
    {
        Storage::fake('local');

        $direction = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);

        $this->actingAs($reception)->post('/api/v1/courriers', [
            'objet' => 'Courrier externe',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $directionDestinataire->id,
            'piece_jointe' => UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf'),
        ])->assertCreated()
            ->assertJsonPath('data.necessite_avis_dg', true);
    }

    public function test_une_direction_ne_peut_pas_usurper_une_autre_direction_dorigine(): void
    {
        $directionEmettrice = Direction::factory()->create();
        $autreDirection = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($directionEmettrice)->create();

        $this->actingAs($responsable)->postJson('/api/v1/courriers', [
            'objet' => 'Tentative usurpation',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_origine_id' => $autreDirection->id,
        ])->assertCreated();

        $this->assertDatabaseHas('courriers', [
            'objet' => 'Tentative usurpation',
            'direction_origine_id' => $directionEmettrice->id,
        ]);
        $this->assertDatabaseMissing('courriers', [
            'objet' => 'Tentative usurpation',
            'direction_origine_id' => $autreDirection->id,
        ]);
    }

    public function test_un_courrier_envoye_conserve_son_contenu_et_sa_piece_jointe_jusqua_consultation(): void
    {
        Storage::fake('local');

        $directionEmettrice = Direction::factory()->create();
        $directionDestinataire = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($directionEmettrice)->create();
        $destinataire = User::factory()->responsableDirection($directionDestinataire)->create();

        // Envoyé exactement comme le fait le formulaire (multipart, contenu
        // TipTap sérialisé en JSON, décodé par StoreCourrierRequest).
        $contenu = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Bonjour, veuillez trouver ci-joint.']]]]];

        $id = $this->actingAs($responsable)->post('/api/v1/courriers', [
            'objet' => 'Convention de partenariat',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $directionDestinataire->id,
            'contenu' => json_encode($contenu),
            'piece_jointe' => UploadedFile::fake()->create('convention.pdf', 200, 'application/pdf'),
        ])->assertCreated()->json('data.id');

        $courrier = Courrier::withoutGlobalScopes()->findOrFail($id);
        $this->assertNotNull($courrier->piece_jointe_chemin);
        Storage::disk('local')->assertExists($courrier->piece_jointe_chemin);
        $this->assertSame($contenu, $courrier->contenu);

        // Consultation par le destinataire : contenu et pièce jointe
        // toujours présents dans la ressource retournée.
        $response = $this->actingAs($destinataire)->getJson("/api/v1/courriers/{$id}")->assertOk();
        $response->assertJsonPath('data.contenu', $contenu);
        $response->assertJsonPath('data.piece_jointe_disponible', true);

        $this->actingAs($destinataire)
            ->get("/api/v1/courriers/{$id}/piece-jointe")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_un_agent_dfp_ne_peut_pas_creer_de_courrier(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        $this->actingAs($dfp)->postJson('/api/v1/courriers', [
            'objet' => 'Test',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
        ])->assertStatus(403);
    }
}
