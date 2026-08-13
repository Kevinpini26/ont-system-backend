<?php

namespace Modules\Kernel\Support;

use Illuminate\Support\Carbon;

/**
 * Bornes d'une période statistique standard (7 derniers jours, 30 jours,
 * année en cours) et de la période précédente équivalente, pour le calcul
 * de variations en pourcentage sur les tableaux de bord. Utilitaire pur
 * (aucune requête, aucune dépendance de domaine) : partagé entre modules
 * via Kernel plutôt que dupliqué, à la différence des utilitaires liés au
 * circuit courrier ou au cycle de vie des stagiaires.
 */
class PeriodeStatistique
{
    public readonly Carbon $debut;

    public readonly Carbon $fin;

    public readonly Carbon $debutPrecedente;

    public readonly Carbon $finPrecedente;

    public readonly string $cle;

    public function __construct(string $cle = '30j')
    {
        $this->cle = in_array($cle, ['7j', '30j', 'annee'], true) ? $cle : '30j';

        [$this->debut, $this->debutPrecedente, $this->finPrecedente] = match ($this->cle) {
            '7j' => [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::now()->subDays(13)->startOfDay(),
                Carbon::now()->subDays(7)->endOfDay(),
            ],
            'annee' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->subYear()->startOfYear(),
                Carbon::now()->subYear()->endOfYear(),
            ],
            default => [
                Carbon::now()->subDays(29)->startOfDay(),
                Carbon::now()->subDays(59)->startOfDay(),
                Carbon::now()->subDays(30)->endOfDay(),
            ],
        };

        $this->fin = Carbon::now()->endOfDay();
    }

    /**
     * Granularité de regroupement pour une courbe d'évolution : par jour
     * sur 7j/30j (trop de points sinon), par mois sur l'année en cours.
     */
    public function granulariteSql(): string
    {
        return $this->cle === 'annee' ? 'month' : 'day';
    }

    public static function variationPourcentage(int|float $actuel, int|float $precedent): ?float
    {
        if ($precedent == 0.0) {
            return $actuel > 0 ? 100.0 : null;
        }

        return round((($actuel - $precedent) / $precedent) * 100, 1);
    }
}
