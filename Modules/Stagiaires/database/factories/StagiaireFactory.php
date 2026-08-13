<?php

namespace Modules\Stagiaires\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Courrier\Models\Courrier;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * @extends Factory<Stagiaire>
 */
class StagiaireFactory extends Factory
{
    protected $model = Stagiaire::class;

    public function definition(): array
    {
        return [
            'courrier_id' => Courrier::factory()->demandeStage(),
            'nom' => fake()->name(),
            'contact' => fake()->safeEmail(),
            'etablissement_origine' => fake()->company(),
            'type_stage' => 'academique',
            'periode_debut_demandee' => now()->addMonth()->toDateString(),
            'periode_fin_demandee' => now()->addMonths(3)->toDateString(),
            'reference_courrier' => 'AR-'.now()->year.'-'.fake()->unique()->numerify('######'),
            'statut' => StagiaireStatut::DOSSIER_RECU,
        ];
    }

    public function professionnel(): static
    {
        return $this->state(fn () => ['type_stage' => 'professionnel']);
    }
}
