<?php

namespace Modules\Stagiaires\Support;

use Modules\Stagiaires\Models\Stagiaire;

/**
 * Suggestion (pas une valeur imposée — l'évaluateur reste libre de
 * l'ajuster, voir GrilleEvaluationForm côté frontend) de ponctualité et de
 * régularité pour la grille d'évaluation, calculée à partir du pointage
 * quotidien plutôt que jugée à l'œil. Référence horaire : les horaires du
 * portail public (Lundi–Vendredi, 8h30–15h30 — voir HORAIRES_PORTAIL,
 * frontend/src/modules/public/constants.js).
 */
class AssiduiteCalculateur
{
    private const HEURE_ARRIVEE_REFERENCE = '08:30:00';

    private const HEURE_DEPART_REFERENCE = '15:30:00';

    /**
     * @return array{ponctualite: float, regularite: float, detail: string}|null null si le stage n'a pas encore commencé
     */
    public static function suggestion(Stagiaire $stagiaire): ?array
    {
        if (! $stagiaire->date_debut_stage) {
            return null;
        }

        $debut = $stagiaire->date_debut_stage->copy()->startOfDay();
        $fin = ($stagiaire->date_fin_stage && $stagiaire->date_fin_stage->isPast())
            ? $stagiaire->date_fin_stage->copy()->startOfDay()
            : now()->startOfDay();

        if ($fin->lt($debut)) {
            return null;
        }

        $joursOuvresEcoules = 0;
        for ($jour = $debut->copy(); $jour->lte($fin); $jour->addDay()) {
            if (! $jour->isWeekend()) {
                $joursOuvresEcoules++;
            }
        }

        $presences = $stagiaire->presences()
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->get();

        $joursAvecArrivee = $presences->whereNotNull('heure_arrivee')->count();

        $joursALHeure = $presences->filter(function ($presence) {
            if ($presence->heure_arrivee === null) {
                return false;
            }
            $arriveeOk = $presence->heure_arrivee <= self::HEURE_ARRIVEE_REFERENCE;
            $departOk = $presence->heure_depart === null || $presence->heure_depart >= self::HEURE_DEPART_REFERENCE;

            return $arriveeOk && $departOk;
        })->count();

        $regularite = $joursOuvresEcoules > 0
            ? min(5.0, round(5 * $joursAvecArrivee / $joursOuvresEcoules))
            : 5.0;

        $ponctualite = $joursAvecArrivee > 0
            ? min(5.0, round(5 * $joursALHeure / $joursAvecArrivee))
            : 5.0;

        $ecarts = $joursAvecArrivee - $joursALHeure;
        $absences = $joursOuvresEcoules - $joursAvecArrivee;

        $detail = "{$joursAvecArrivee} jour(s) pointé(s) sur {$joursOuvresEcoules} jour(s) ouvré(s) écoulé(s)"
            .($absences > 0 ? " · {$absences} absence(s)" : '')
            .($ecarts > 0 ? " · {$ecarts} écart(s) de ponctualité" : '');

        return [
            'ponctualite' => (float) $ponctualite,
            'regularite' => (float) $regularite,
            'detail' => $detail,
        ];
    }
}
