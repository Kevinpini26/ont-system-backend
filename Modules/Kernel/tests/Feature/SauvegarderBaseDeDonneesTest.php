<?php

namespace Modules\Kernel\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SauvegarderBaseDeDonneesTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * pg_dump n'est pas relancé réellement (Process::fake) : ce test valide
     * l'orchestration de la commande (upload + rétention), pas pg_dump
     * lui-même — déjà vérifié manuellement contre la base de développement
     * réelle (dump gzip valide produit et restauré avec succès).
     */
    public function test_la_sauvegarde_est_envoyee_sur_le_disque_configure_et_la_retention_est_appliquee(): void
    {
        Storage::fake('backups-local');
        config(['backup.disk' => 'backups-local', 'backup.retention_days' => 30]);

        Carbon::setTestNow('2026-06-15 03:00:00');
        $cheminTemporaire = storage_path('app/tmp-backup-ont-2026-06-15_030000.sql.gz');

        Process::fake(function () use ($cheminTemporaire) {
            // Simule la production du fichier gzip par le pipeline shell
            // réel (pg_dump | gzip > fichier), sans exécuter pg_dump.
            file_put_contents($cheminTemporaire, 'dump-simule');

            return Process::result(output: '', errorOutput: '', exitCode: 0);
        });

        // Ancienne sauvegarde à purger (au-delà de la rétention).
        Storage::disk('backups-local')->put('database/ont-ancienne.sql.gz', 'ancien');
        touch(Storage::disk('backups-local')->path('database/ont-ancienne.sql.gz'), now()->subDays(45)->timestamp);

        $this->artisan('kernel:sauvegarder-base-de-donnees')->assertSuccessful();

        $fichiers = Storage::disk('backups-local')->files('database');

        $this->assertCount(1, $fichiers);
        $this->assertStringNotContainsString('ancienne', $fichiers[0]);
    }
}
