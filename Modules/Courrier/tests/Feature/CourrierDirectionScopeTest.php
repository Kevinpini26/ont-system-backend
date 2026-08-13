<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;

class CourrierDirectionScopeTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_un_responsable_de_direction_voit_ses_courriers_emis_et_recus_uniquement(): void
    {
        $directionA = Direction::factory()->create();
        $directionB = Direction::factory()->create();

        Courrier::factory()->create(['direction_origine_id' => $directionA->id, 'direction_destination_id' => $directionB->id]);
        Courrier::factory()->create(['direction_origine_id' => $directionB->id, 'direction_destination_id' => $directionA->id]);
        Courrier::factory()->create(['direction_origine_id' => $directionB->id, 'direction_destination_id' => $directionB->id]);

        $responsableA = User::factory()->responsableDirection($directionA)->create();

        $this->actingAs($responsableA)
            ->getJson('/api/v1/courriers')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_les_postes_du_circuit_central_voient_tous_les_courriers(): void
    {
        $directionA = Direction::factory()->create();
        $directionB = Direction::factory()->create();

        Courrier::factory()->count(3)->create(['direction_origine_id' => $directionA->id, 'direction_destination_id' => $directionB->id]);

        $reception = $this->agent(Poste::RECEPTION, $directionB);

        $this->actingAs($reception)
            ->getJson('/api/v1/courriers')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
