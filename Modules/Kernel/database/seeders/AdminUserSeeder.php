<?php

namespace Modules\Kernel\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Compte administrateur initial permettant le premier accès au système.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@ont.cd'],
            [
                'name' => 'Administrateur ONT',
                // Mot de passe respectant la politique minimale (10 car.,
                // majuscule + minuscule + chiffre) — À CHANGER IMMÉDIATEMENT
                // après le premier déploiement en production, voir SECURITY.md.
                'password' => 'ChangeMoi#ONT2026',
                'role' => UserRole::ADMINISTRATEUR,
                'poste' => null,
                'direction_id' => null,
                'email_verified_at' => now(),
            ],
        );
    }
}
