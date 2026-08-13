<?php

namespace Modules\Stagiaires\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Disponibilité des demandes de stage par type (académique / professionnel)
 * — ligne singleton (id=1), un seul état partagé par toute la plateforme.
 * Consultée à la fois par la page publique de dépôt (sans authentification)
 * et par DeposerDemandeStageRequest pour un rejet serveur, pas seulement
 * cosmétique côté formulaire.
 */
class DisponibiliteDemandesStage extends Model
{
    protected $table = 'disponibilite_demandes_stage';

    protected $fillable = [
        'academique_ouvert',
        'professionnel_ouvert',
    ];

    protected function casts(): array
    {
        return [
            'academique_ouvert' => 'boolean',
            'professionnel_ouvert' => 'boolean',
        ];
    }

    public static function actuelle(): self
    {
        // Pas de filtre sur un id supposé fixe : 'id' n'étant pas fillable,
        // un firstOrCreate(['id' => 1], ...) fige silencieusement l'id (mass
        // assignment l'ignore) et peut créer une ligne orpheline si la
        // séquence a avancé au-delà de 1 (ex. lignes précédentes supprimées).
        // "La première ligne trouvée, sinon on la crée" est le seul
        // invariant réellement nécessaire pour un singleton.
        return self::query()->first() ?? self::query()->create(['academique_ouvert' => true, 'professionnel_ouvert' => true]);
    }

    public function ouvertPourType(string $type): bool
    {
        return $type === 'professionnel' ? $this->professionnel_ouvert : $this->academique_ouvert;
    }
}
