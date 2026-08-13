<?php

namespace Modules\Stagiaires\Support;

/**
 * Grille d'évaluation officielle ONT pour le stage professionnel —
 * distincte de GrilleEvaluation (stage académique), jamais mélangées (voir
 * Modules\Stagiaires\Enums\StagiaireTypeStage::classeGrille()). Dix
 * rubriques réparties en trois catégories, chacune notée sur 10 (10×10 =
 * 100) — contrairement à la grille académique, pas de champ "justification"
 * par catégorie : non prévu dans cette grille officielle.
 */
class GrilleEvaluationProfessionnelle
{
    /**
     * @return array<string, array<string>>
     */
    private static function sections(): array
    {
        return [
            'aspects_intellectuels' => [
                'connaissance_metier', 'esprit_initiative_responsabilite', 'capacite_ecoute_communication',
            ],
            'aspects_humains' => [
                'assiduite_discipline', 'relation_interpersonnelle', 'ponctualite_regularite', 'presentation_contacts',
            ],
            'aspects_professionnels' => [
                'efficacite_rendement', 'capacite_innovation', 'maitrise_langue',
            ],
        ];
    }

    /**
     * @return array<string, array> règles de validation Laravel imbriquées, à fusionner dans rules()
     */
    public static function rules(string $prefix = 'grille'): array
    {
        $regles = [];

        foreach (self::sections() as $section => $criteres) {
            foreach ($criteres as $critere) {
                $regles["{$prefix}.{$section}.{$critere}"] = ['required', 'numeric', 'min:0', 'max:10'];
            }
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
