<?php

namespace Modules\Courrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Courrier\Enums\CourrierType;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;

/**
 * @extends Factory<Courrier>
 */
class CourrierFactory extends Factory
{
    protected $model = Courrier::class;

    public function definition(): array
    {
        static $sequence = 0;
        $sequence++;

        return [
            'numero_accuse_reception' => sprintf('AR-%d-%06d', now()->year, $sequence),
            'objet' => fake()->sentence(),
            'contenu' => ['type' => 'doc', 'content' => []],
            'type' => CourrierType::CORRESPONDANCE_GENERALE,
            'statut' => CourrierStatut::RECU,
            'direction_origine_id' => Direction::factory(),
            'direction_destination_id' => Direction::factory(),
            'created_by' => User::factory()->administrateur(),
        ];
    }

    public function demandeStage(): static
    {
        return $this->state(fn () => [
            'type' => CourrierType::DEMANDE_STAGE,
            'candidat_nom' => fake()->name(),
            'candidat_contact' => fake()->safeEmail(),
            'candidat_etablissement' => fake()->company(),
            'type_stage' => 'academique',
            'periode_souhaitee_debut' => now()->addMonth()->toDateString(),
            'periode_souhaitee_fin' => now()->addMonths(3)->toDateString(),
        ]);
    }

    public function professionnel(): static
    {
        return $this->state(fn () => ['type_stage' => 'professionnel']);
    }
}
