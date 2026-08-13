<?php

namespace Modules\Kernel\Database\Seeders;

use Illuminate\Database\Seeder;

class KernelDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DirectionSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Comptes de démonstration (un par poste du circuit courrier, DFP,
        // un responsable par direction) : uniquement en local, jamais en
        // production — voir DemoAccountsSeeder.
        if (app()->environment('local')) {
            $this->call(DemoAccountsSeeder::class);
        }
    }
}
