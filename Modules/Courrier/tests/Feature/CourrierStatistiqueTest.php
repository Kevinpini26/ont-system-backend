<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Courrier\Enums\CourrierType;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Tests\TestCase;

class CourrierStatistiqueTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_le_tableau_de_bord_expose_la_repartition_par_statut_et_le_temps_de_traitement(): void
    {
        Storage::fake('local');

        $direction = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);
        $protocole = $this->agent(Poste::PROTOCOLE, $direction);

        $id = $this->actingAs($reception)->post('/api/v1/courriers', [
            'objet' => 'Test statistiques',
            'type' => CourrierType::CORRESPONDANCE_GENERALE->value,
            'direction_destination_id' => $direction->id,
            'piece_jointe' => UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf'),
        ])->json('data.id');

        $this->actingAs($protocole)->postJson("/api/v1/courriers/{$id}/accuser-reception")->assertOk();

        $this->actingAs($protocole)
            ->postJson("/api/v1/courriers/{$id}/transmettre-protocole")
            ->assertOk();

        $response = $this->actingAs($protocole)
            ->getJson('/api/v1/courriers/statistiques')
            ->assertOk();

        $response->assertJsonStructure([
            'par_statut',
            'en_cours_total',
            'en_attente_relecture',
            'temps_moyen_par_etape',
        ]);

        $parStatut = collect($response->json('par_statut'))->keyBy('statut');
        $this->assertSame(1, $parStatut['au_protocole']['total']);
    }

    public function test_une_direction_ne_peut_pas_consulter_le_tableau_de_bord_du_circuit(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        $this->actingAs($responsable)
            ->getJson('/api/v1/courriers/statistiques')
            ->assertStatus(403);
    }

    public function test_le_tableau_de_bord_expose_la_variation_et_levolution_sur_la_periode(): void
    {
        $direction = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);

        \Modules\Courrier\Models\Courrier::factory()->count(3)->create(['created_at' => now()]);
        \Modules\Courrier\Models\Courrier::factory()->count(2)->create(['created_at' => now()->subDays(45)]);

        $response = $this->actingAs($reception)
            ->getJson('/api/v1/courriers/statistiques?periode=30j')
            ->assertOk();

        $response->assertJsonStructure(['courriers_recus_periode', 'courriers_recus_variation', 'periode' => ['cle', 'depuis', 'jusqua'], 'evolution']);
        $this->assertSame('30j', $response->json('periode.cle'));
        $this->assertGreaterThanOrEqual(3, $response->json('courriers_recus_periode'));
    }

    public function test_le_tableau_de_bord_dune_direction_daccueil_ne_montre_que_ses_propres_courriers(): void
    {
        $direction = Direction::factory()->create();
        $autreDirection = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        \Modules\Courrier\Models\Courrier::factory()->create(['direction_destination_id' => $direction->id, 'direction_origine_id' => null]);
        \Modules\Courrier\Models\Courrier::factory()->create(['direction_origine_id' => $direction->id, 'direction_destination_id' => null]);
        \Modules\Courrier\Models\Courrier::factory()->create(['direction_destination_id' => $autreDirection->id, 'direction_origine_id' => null]);

        $response = $this->actingAs($responsable)
            ->getJson('/api/v1/courriers/statistiques-direction')
            ->assertOk();

        $this->assertSame(1, $response->json('courriers_recus_periode'));
        $this->assertSame(1, $response->json('courriers_emis_periode'));
        $this->assertSame(1, $response->json('courriers_recus_non_traites'));
        $this->assertSame(1, $response->json('courriers_emis_en_cours'));
    }

    public function test_les_files_dattente_ne_comptent_pas_les_courriers_deja_enregistres(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        \Modules\Courrier\Models\Courrier::factory()->create([
            'direction_destination_id' => $direction->id,
            'direction_origine_id' => null,
            'statut' => \Modules\Courrier\Enums\CourrierStatut::ENREGISTRE,
        ]);

        $response = $this->actingAs($responsable)
            ->getJson('/api/v1/courriers/statistiques-direction')
            ->assertOk();

        $this->assertSame(0, $response->json('courriers_recus_non_traites'));
    }

    public function test_un_agent_du_circuit_ne_peut_pas_consulter_le_tableau_de_bord_dune_direction(): void
    {
        $direction = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);

        $this->actingAs($reception)
            ->getJson('/api/v1/courriers/statistiques-direction')
            ->assertStatus(403);
    }

    public function test_la_dfp_peut_consulter_le_tableau_de_bord_de_son_propre_perimetre_courrier(): void
    {
        $directionDfp = Direction::factory()->create();
        $dfp = User::factory()->agentDfp()->create(['direction_id' => $directionDfp->id]);

        \Modules\Courrier\Models\Courrier::factory()->create(['direction_destination_id' => $directionDfp->id, 'direction_origine_id' => null]);

        $response = $this->actingAs($dfp)
            ->getJson('/api/v1/courriers/statistiques-direction')
            ->assertOk();

        $this->assertSame(1, $response->json('courriers_recus_non_traites'));
    }
}
