<?php

namespace Modules\Stagiaires\Support;

use Modules\Stagiaires\Contracts\DoublonDetector;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * Comparaison approximative en PHP (similar_text), sans dépendance
 * PostgreSQL supplémentaire (pas d'extension pg_trgm à activer) : suffisant
 * tant que le volume de stagiaires reste de l'ordre de quelques milliers —
 * au-delà, une recherche floue côté base de données serait préférable.
 */
class SimilarTextDoublonDetector implements DoublonDetector
{
    private const SEUIL_POURCENTAGE = 72.0;

    public function trouverDoublon(Stagiaire $candidat): ?Stagiaire
    {
        $nomCandidat = $this->normaliser($candidat->nom);
        $etablissementCandidat = $this->normaliser($candidat->etablissement_origine);

        return Stagiaire::query()
            ->withoutGlobalScopes()
            ->whereKeyNot($candidat->id)
            ->get(['id', 'nom', 'etablissement_origine', 'direction_id', 'statut', 'courrier_id'])
            ->first(function (Stagiaire $existant) use ($nomCandidat, $etablissementCandidat) {
                $similariteNom = $this->similarite($nomCandidat, $this->normaliser($existant->nom));
                $similariteEtablissement = $this->similarite(
                    $etablissementCandidat,
                    $this->normaliser($existant->etablissement_origine),
                );

                return $similariteNom >= self::SEUIL_POURCENTAGE && $similariteEtablissement >= self::SEUIL_POURCENTAGE;
            });
    }

    private function normaliser(string $valeur): string
    {
        return mb_strtolower(trim($valeur));
    }

    private function similarite(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $pourcentage);

        return $pourcentage;
    }
}
