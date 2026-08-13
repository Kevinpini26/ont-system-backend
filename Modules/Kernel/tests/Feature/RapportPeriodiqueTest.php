<?php

namespace Modules\Kernel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Tests\TestCase;

class RapportPeriodiqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_ladministrateur_peut_generer_le_rapport_periodique_mensuel(): void
    {
        $admin = User::factory()->administrateur()->create();

        $response = $this->actingAs($admin)->get('/api/v1/rapports/periodique?'.http_build_query([
            'type' => 'mois',
            'annee' => now()->year,
            'mois' => now()->month,
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_ladministrateur_peut_generer_le_rapport_periodique_annuel(): void
    {
        $admin = User::factory()->administrateur()->create();

        $response = $this->actingAs($admin)->get('/api/v1/rapports/periodique?'.http_build_query([
            'type' => 'annee',
            'annee' => now()->year,
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_un_agent_non_administrateur_ne_peut_pas_generer_le_rapport(): void
    {
        $direction = Direction::factory()->create();
        $responsable = User::factory()->responsableDirection($direction)->create();

        $this->actingAs($responsable)
            ->get('/api/v1/rapports/periodique?type=annee&annee='.now()->year)
            ->assertStatus(403);
    }

    public function test_le_mois_est_obligatoire_pour_un_rapport_mensuel(): void
    {
        $admin = User::factory()->administrateur()->create();

        $this->actingAs($admin)
            ->get('/api/v1/rapports/periodique?type=mois&annee='.now()->year)
            ->assertStatus(422);
    }
}
