<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;

/**
 * CourrierController::index() ne lisait auparavant aucun paramètre de
 * requête — le filtre "statut" déjà câblé côté CourrierDgDashboardPage.jsx
 * était silencieusement ignoré côté serveur.
 */
class RechercheEtFiltrageCourrierTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_le_filtre_statut_est_applique(): void
    {
        Courrier::factory()->create(['statut' => CourrierStatut::RECU]);
        Courrier::factory()->create(['statut' => CourrierStatut::ENREGISTRE]);

        $direction = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);

        $this->actingAs($reception)
            ->getJson('/api/v1/courriers?statut=recu')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statut', 'recu');
    }

    public function test_la_recherche_libre_porte_sur_objet_et_numero(): void
    {
        Courrier::factory()->create(['objet' => 'Demande de partenariat touristique']);
        Courrier::factory()->create(['objet' => 'Autre chose']);

        $direction = Direction::factory()->create();
        $reception = $this->agent(Poste::RECEPTION, $direction);

        $this->actingAs($reception)
            ->getJson('/api/v1/courriers?recherche=partenariat')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_le_filtre_direction_destination_est_applique(): void
    {
        $directionA = Direction::factory()->create();
        $directionB = Direction::factory()->create();

        Courrier::factory()->create(['direction_destination_id' => $directionA->id]);
        Courrier::factory()->create(['direction_destination_id' => $directionB->id]);

        $reception = $this->agent(Poste::RECEPTION, $directionA);

        $this->actingAs($reception)
            ->getJson("/api/v1/courriers?direction_destination_id={$directionA->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
