<?php

namespace Modules\Stagiaires\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Models\DisponibiliteDemandesStage;

class DisponibiliteDemandesStageTest extends StagiaireTestCase
{
    use RefreshDatabase;

    public function test_la_disponibilite_est_ouverte_par_defaut(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        $this->actingAs($dfp)
            ->getJson('/api/v1/stagiaires/disponibilite-demandes')
            ->assertOk()
            ->assertJson(['academique' => true, 'professionnel' => true]);
    }

    public function test_la_dfp_peut_fermer_un_type_de_demande(): void
    {
        $dfp = User::factory()->agentDfp()->create();

        $this->actingAs($dfp)
            ->postJson('/api/v1/stagiaires/disponibilite-demandes', ['type' => 'professionnel', 'ouvert' => false])
            ->assertOk()
            ->assertJson(['academique' => true, 'professionnel' => false]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'demande_stage.disponibilite_modifiee']);
    }

    public function test_un_role_autre_que_dfp_ne_peut_pas_modifier_la_disponibilite(): void
    {
        $responsable = User::factory()->responsableDirection()->create();

        $this->actingAs($responsable)
            ->postJson('/api/v1/stagiaires/disponibilite-demandes', ['type' => 'academique', 'ouvert' => false])
            ->assertForbidden();
    }

    public function test_la_lecture_publique_ne_necessite_aucune_authentification(): void
    {
        DisponibiliteDemandesStage::query()->create(['academique_ouvert' => true, 'professionnel_ouvert' => false]);

        $this->getJson('/api/v1/public/disponibilite-demandes-stage')
            ->assertOk()
            ->assertJson(['academique' => true, 'professionnel' => false]);
    }
}
