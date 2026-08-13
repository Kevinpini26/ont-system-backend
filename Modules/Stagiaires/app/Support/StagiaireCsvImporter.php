<?php

namespace Modules\Stagiaires\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Kernel\Models\Direction;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Enums\StagiaireOrigine;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * Import de dossiers de stagiaires antérieurs au lancement du système
 * (aucun courrier de demande de stage associé — voir la migration
 * 2026_06_01_000001). Ne fait pas partie des points d'extension SOLID du
 * module : il n'existe pas de second format d'import à substituer ici, une
 * interface serait de la sur-ingénierie pour un outil d'administration
 * ponctuel.
 */
class StagiaireCsvImporter
{
    /**
     * En-têtes attendues, dans cet ordre exact. `statut`, `contact`,
     * `direction_code`, `date_debut_stage`, `date_fin_stage`,
     * `note_finale` et `reference_courrier` sont optionnelles (colonne
     * vide acceptée) ; `nom` et `etablissement_origine` sont obligatoires.
     */
    private const ENTETES_ATTENDUES = [
        'nom', 'contact', 'etablissement_origine', 'direction_code',
        'statut', 'date_debut_stage', 'date_fin_stage', 'note_finale', 'reference_courrier',
    ];

    /**
     * @return array{importes: int, total_lignes: int, rejetes: array<int, array{ligne: int, raisons: string[]}>}
     */
    public function importer(UploadedFile $fichier, User $administrateur): array
    {
        $poignee = fopen($fichier->getRealPath(), 'r');

        if ($poignee === false) {
            return ['importes' => 0, 'total_lignes' => 0, 'rejetes' => [['ligne' => 0, 'raisons' => ['Fichier illisible.']]]];
        }

        $entetes = fgetcsv($poignee);

        if ($entetes === false || $this->normaliserEntetes($entetes) !== self::ENTETES_ATTENDUES) {
            fclose($poignee);

            return [
                'importes' => 0,
                'total_lignes' => 0,
                'rejetes' => [[
                    'ligne' => 1,
                    'raisons' => ['En-têtes invalides. Colonnes attendues : '.implode(', ', self::ENTETES_ATTENDUES).'.'],
                ]],
            ];
        }

        $directionsParCode = Direction::query()->pluck('id', 'code');
        $statutsValides = array_map(fn (StagiaireStatut $s) => $s->value, StagiaireStatut::cases());

        $importes = 0;
        $rejetes = [];
        $numeroLigne = 1;
        $aImporter = [];

        while (($ligne = fgetcsv($poignee)) !== false) {
            $numeroLigne++;

            if (count(array_filter($ligne, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue; // ligne vide
            }

            $donnees = array_combine(self::ENTETES_ATTENDUES, array_pad($ligne, count(self::ENTETES_ATTENDUES), null));
            $raisons = $this->validerLigne($donnees, $directionsParCode, $statutsValides);

            if ($raisons !== []) {
                $rejetes[] = ['ligne' => $numeroLigne, 'raisons' => $raisons];

                continue;
            }

            $aImporter[] = $this->normaliserLigne($donnees, $directionsParCode, $administrateur);
        }

        fclose($poignee);

        if ($aImporter !== []) {
            DB::transaction(function () use ($aImporter, &$importes) {
                foreach ($aImporter as $donnees) {
                    Stagiaire::query()->create($donnees);
                    $importes++;
                }
            });
        }

        return [
            'importes' => $importes,
            'total_lignes' => $numeroLigne - 1,
            'rejetes' => $rejetes,
        ];
    }

    private function normaliserEntetes(array $entetes): array
    {
        return array_map(fn ($e) => mb_strtolower(trim((string) $e)), $entetes);
    }

    /**
     * @return string[]
     */
    private function validerLigne(array $donnees, $directionsParCode, array $statutsValides): array
    {
        $raisons = [];

        if (trim((string) $donnees['nom']) === '') {
            $raisons[] = 'Le nom est obligatoire.';
        }

        if (trim((string) $donnees['etablissement_origine']) === '') {
            $raisons[] = "L'établissement d'origine est obligatoire.";
        }

        if (! empty($donnees['direction_code']) && ! $directionsParCode->has(strtoupper(trim($donnees['direction_code'])))) {
            $raisons[] = "Code direction inconnu : « {$donnees['direction_code']} ».";
        }

        if (! empty($donnees['statut']) && ! in_array($donnees['statut'], $statutsValides, true)) {
            $raisons[] = "Statut invalide : « {$donnees['statut']} » (attendu : ".implode(', ', $statutsValides).').';
        }

        foreach (['date_debut_stage', 'date_fin_stage'] as $champ) {
            if (! empty($donnees[$champ]) && ! $this->estDateValide($donnees[$champ])) {
                $raisons[] = "Date invalide pour {$champ} (format attendu AAAA-MM-JJ) : « {$donnees[$champ]} ».";
            }
        }

        if (! empty($donnees['note_finale'])) {
            $note = str_replace(',', '.', $donnees['note_finale']);
            if (! is_numeric($note) || $note < 0 || $note > 20) {
                $raisons[] = "Note finale invalide (attendu entre 0 et 20) : « {$donnees['note_finale']} ».";
            }
        }

        return $raisons;
    }

    private function estDateValide(string $valeur): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $valeur);

        return $date !== false && $date->format('Y-m-d') === $valeur;
    }

    private function normaliserLigne(array $donnees, $directionsParCode, User $administrateur): array
    {
        $directionId = ! empty($donnees['direction_code'])
            ? $directionsParCode->get(strtoupper(trim($donnees['direction_code'])))
            : null;

        return [
            'nom' => trim($donnees['nom']),
            'contact' => trim((string) $donnees['contact']) ?: 'non renseigné',
            'etablissement_origine' => trim($donnees['etablissement_origine']),
            'direction_id' => $directionId,
            'statut' => ! empty($donnees['statut']) ? $donnees['statut'] : StagiaireStatut::CLOTURE->value,
            'date_debut_stage' => $donnees['date_debut_stage'] ?: null,
            'date_fin_stage' => $donnees['date_fin_stage'] ?: null,
            'note_finale' => ! empty($donnees['note_finale']) ? (float) str_replace(',', '.', $donnees['note_finale']) : null,
            'reference_courrier' => ! empty($donnees['reference_courrier']) ? trim($donnees['reference_courrier']) : 'HIST-'.uniqid(),
            'origine' => StagiaireOrigine::IMPORT_HISTORIQUE,
            'importe_par_id' => $administrateur->id,
            'importe_at' => now(),
        ];
    }
}
