<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Kernel\Database\Seeders\KernelDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KernelDatabaseSeeder::class,
        ]);
    }
}
