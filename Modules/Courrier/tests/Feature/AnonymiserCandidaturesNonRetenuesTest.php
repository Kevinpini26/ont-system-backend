<?php

namespace Modules\Courrier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Courrier\Console\AnonymiserCandidaturesNonRetenuesCommand;
use Modules\Courrier\Enums\AvisDg;
use Modules\Courrier\Enums\CourrierType;
use Modules\Courrier\Models\Courrier;
use Modules\Courrier\Models\CourrierAnnotation;
use Modules\Kernel\Models\User;

class AnonymiserCandidaturesNonRetenuesTest extends CourrierTestCase
{
    use RefreshDatabase;

    public function test_une_candidature_non_retenue_depuis_plus_de_12_mois_est_anonymisee(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'avis_dg' => AvisDg::DEFAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(13),
            'avis_dg_commentaire' => 'Profil ne correspondant pas aux besoins.',
            'note_technique' => 'Voir dossier papier.',
        ]);
        CourrierAnnotation::query()->create([
            'courrier_id' => $courrier->id,
            'auteur_id' => User::factory()->create()->id,
            'contenu' => 'Note interne sur le candidat.',
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();

        $courrier->refresh();
        $this->assertNull($courrier->candidat_nom);
        $this->assertNull($courrier->candidat_contact);
        $this->assertNull($courrier->candidat_etablissement);
        $this->assertNull($courrier->avis_dg_commentaire);
        $this->assertNull($courrier->note_technique);
        $this->assertSame('Candidature anonymisée', $courrier->objet);
        $this->assertNotNull($courrier->anonymise_at);
        $this->assertCount(0, $courrier->annotations()->get());

        // Les données statistiques, elles, restent intactes.
        $this->assertSame(AvisDg::DEFAVORABLE, $courrier->avis_dg);
        $this->assertSame(CourrierType::DEMANDE_STAGE, $courrier->type);
        $this->assertNotNull($courrier->direction_destination_id);
    }

    public function test_une_candidature_non_retenue_depuis_moins_de_12_mois_nest_pas_anonymisee(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'avis_dg' => AvisDg::DEFAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(6),
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();

        $courrier->refresh();
        $this->assertNotNull($courrier->candidat_nom);
        $this->assertNull($courrier->anonymise_at);
    }

    public function test_une_candidature_retenue_avis_favorable_nest_jamais_anonymisee(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'avis_dg' => AvisDg::FAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(24),
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();

        $courrier->refresh();
        $this->assertNotNull($courrier->candidat_nom);
        $this->assertNull($courrier->anonymise_at);
    }

    public function test_un_courrier_qui_nest_pas_une_demande_de_stage_nest_pas_anonymise(): void
    {
        $courrier = Courrier::factory()->create([
            'avis_dg' => AvisDg::DEFAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(24),
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();

        $this->assertNull($courrier->fresh()->anonymise_at);
    }

    public function test_une_seconde_execution_ne_retraite_pas_un_dossier_deja_anonymise(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'avis_dg' => AvisDg::DEFAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(13),
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();
        $premiereAnonymisation = $courrier->fresh()->anonymise_at;

        $this->travel(1)->days();
        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class)->assertSuccessful();

        $this->assertEquals($premiereAnonymisation, $courrier->fresh()->anonymise_at);
    }

    public function test_le_seuil_de_conservation_est_configurable(): void
    {
        $courrier = Courrier::factory()->demandeStage()->create([
            'avis_dg' => AvisDg::DEFAVORABLE,
            'avis_dg_rendu_at' => now()->subMonths(2),
        ]);

        $this->artisan(AnonymiserCandidaturesNonRetenuesCommand::class, ['--seuil-mois' => 1])->assertSuccessful();

        $this->assertNotNull($courrier->fresh()->anonymise_at);
    }
}
