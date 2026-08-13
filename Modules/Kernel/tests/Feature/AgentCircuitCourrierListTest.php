<?php

namespace Modules\Kernel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Tests\TestCase;

class AgentCircuitCourrierListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tout_utilisateur_authentifie_peut_lister_les_agents_du_circuit_courrier(): void
    {
        $direction = Direction::factory()->create();
        User::factory()->agentCircuitCourrier(Poste::SECRETARIAT_2, $direction)->create(['name' => 'Jean Kalala']);
        $responsable = User::factory()->responsableDirection($direction)->create();

        $response = $this->actingAs($responsable)->getJson('/api/v1/agents-circuit-courrier');

        $response->assertOk()->assertJsonFragment(['name' => 'Jean Kalala']);
        $this->assertArrayNotHasKey('email', $response->json('data.0'));
    }
}
