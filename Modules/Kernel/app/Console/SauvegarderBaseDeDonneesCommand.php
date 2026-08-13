<?php

namespace Modules\Kernel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Exporte quotidiennement la base PostgreSQL (pg_dump) vers un stockage
 * distinct du serveur applicatif (voir config/backup.php), avec rétention
 * glissante. Planifiée dans KernelServiceProvider::configureSchedules.
 */
class SauvegarderBaseDeDonneesCommand extends Command
{
    protected $signature = 'kernel:sauvegarder-base-de-donnees';

    protected $description = 'Exporte la base de données vers le disque de sauvegarde configuré (voir config/backup.php)';

    public function handle(): int
    {
        $connexion = config('database.default');
        $config = config("database.connections.{$connexion}");

        if (($config['driver'] ?? null) !== 'pgsql') {
            $this->error("Cette commande ne prend en charge que PostgreSQL (connexion configurée : {$config['driver']}).");

            return self::FAILURE;
        }

        $nomFichier = 'ont-'.now()->format('Y-m-d_His').'.sql.gz';
        $cheminTemporaire = storage_path("app/tmp-backup-{$nomFichier}");

        $resultat = Process::env(['PGPASSWORD' => $config['password']])
            ->run([
                'sh', '-c',
                sprintf(
                    'pg_dump --host=%s --port=%s --username=%s --no-password %s | gzip > %s',
                    escapeshellarg($config['host']),
                    escapeshellarg((string) $config['port']),
                    escapeshellarg($config['username']),
                    escapeshellarg($config['database']),
                    escapeshellarg($cheminTemporaire),
                ),
            ]);

        if ($resultat->failed()) {
            $this->error('Échec de pg_dump : '.$resultat->errorOutput());

            return self::FAILURE;
        }

        $disque = config('backup.disk');
        $cheminDistant = 'database/'.$nomFichier;

        Storage::disk($disque)->put($cheminDistant, file_get_contents($cheminTemporaire));
        unlink($cheminTemporaire);

        $this->info("Sauvegarde envoyée sur le disque « {$disque} » : {$cheminDistant}");

        $this->nettoyerAnciennesSauvegardes($disque);

        return self::SUCCESS;
    }

    private function nettoyerAnciennesSauvegardes(string $disque): void
    {
        $seuil = now()->subDays((int) config('backup.retention_days'));
        $supprimees = 0;

        foreach (Storage::disk($disque)->files('database') as $fichier) {
            $modifie = Storage::disk($disque)->lastModified($fichier);

            if ($modifie !== false && now()->createFromTimestamp($modifie)->lt($seuil)) {
                Storage::disk($disque)->delete($fichier);
                $supprimees++;
            }
        }

        if ($supprimees > 0) {
            $this->info("{$supprimees} ancienne(s) sauvegarde(s) supprimée(s) (rétention : ".config('backup.retention_days').' jours).');
        }
    }
}
