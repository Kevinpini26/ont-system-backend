<?php

namespace Modules\Public\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Stagiaires\Models\Stagiaire;
use Tests\TestCase;

class VerificationAttestationPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_tiers_peut_verifier_une_attestation_sans_authentification(): void
    {
        Stagiaire::factory()->create([
            'nom' => 'Jean Kabila',
            'numero_attestation' => 'ATT-2026-000042',
            'date_debut_stage' => '2026-01-05',
            'date_fin_stage' => '2026-03-05',
            'etablissement_origine' => 'Université de Kinshasa',
            'evaluation_direction_total' => 78,
        ]);

        $response = $this->getJson('/api/v1/public/attestations/ATT-2026-000042');

        $response->assertOk()
            ->assertJsonPath('data.numero_attestation', 'ATT-2026-000042')
            ->assertJsonPath('data.nom', 'Jean Kabila')
            ->assertJsonPath('data.date_debut_stage', '2026-01-05')
            ->assertJsonPath('data.date_fin_stage', '2026-03-05')
            ->assertJsonMissing(['etablissement_origine'])
            ->assertJsonMissing(['evaluation_direction_total']);
    }

    public function test_un_numero_inconnu_renvoie_404(): void
    {
        $this->getJson('/api/v1/public/attestations/ATT-2026-999999')
            ->assertStatus(404);
    }
}
