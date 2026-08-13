<?php

namespace Modules\Stagiaires\Support;

/**
 * Grille d'évaluation officielle ONT — reproduite exactement (mêmes
 * intitulés, mêmes barèmes, mêmes regroupements) : trois sections, chacune
 * notée sur un sous-total (50 + 30 + 20 = 100), avec une justification
 * libre par section. Utilisée à l'identique par la direction d'accueil et
 * par la DFP, chacune remplissant sa propre copie indépendamment (voir
 * StagiaireCircuitService::evaluerParDirection()/evaluerParDfp()).
 */
class GrilleEvaluation
{
    /**
     * @return array<string, array<string>> section => critères notés sur 10 (le reste de la section est noté sur 5)
     */
    private static function sections(): array
    {
        return [
            'aptitudes_professionnelles' => [
                'connaissance_metier', 'esprit_initiative', 'sens_responsabilite', 'soin_proprete', 'rendement',
            ],
            'relations_humaines' => [
                'esprit_equipe', 'communication', 'relations_sociales',
            ],
            'presentation' => [
                'discipline', 'ponctualite', 'regularite', 'tenue',
            ],
        ];
    }

    /**
     * Barème max par critère : 10 pour les deux premières sections, 5 pour
     * "Présentation" (4 critères × 5 = 20).
     */
    private static function maxParCritere(string $section): int
    {
        return $section === 'presentation' ? 5 : 10;
    }

    /**
     * @return array<string, array> règles de validation Laravel imbriquées, à fusionner dans rules()
     */
    public static function rules(string $prefix = 'grille'): array
    {
        $regles = [];

        foreach (self::sections() as $section => $criteres) {
            $max = self::maxParCritere($section);
            foreach ($criteres as $critere) {
                $regles["{$prefix}.{$section}.{$critere}"] = ['required', 'numeric', 'min:0', "max:{$max}"];
            }
            $regles["{$prefix}.{$section}.justification"] = ['nullable', 'string'];
        }

        return $regles;
    }

    /**
     * Total général sur 100, toujours recalculé côté serveur — jamais fait
     * confiance à un total envoyé par le client.
     */
    public static function total(array $grille): float
    {
        $total = 0.0;

        foreach (self::sections() as $section => $criteres) {
            foreach ($criteres as $critere) {
                $total += (float) ($grille[$section][$critere] ?? 0);
            }
        }

        return round($total, 2);
    }
}
