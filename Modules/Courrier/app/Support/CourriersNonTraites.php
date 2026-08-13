<?php

namespace Modules\Courrier\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Courrier\Enums\CourrierStatut;

/**
 * "Non traité depuis plus de X" : signal d'alerte réutilisé à trois
 * endroits (CourrierStatistiqueController::dg() sans filtre de direction,
 * ::pourDirection() et NotificationCompteurController pour une direction
 * précise) — un seul point de vérité pour la requête plutôt que trois
 * copies de la même jointure sur la dernière transition de chaque courrier.
 */
class CourriersNonTraites
{
    public static function compter(Carbon $seuil, ?int $directionId = null): int
    {
        $conditionDirection = $directionId !== null ? 'and c.direction_destination_id = ?' : '';

        $bindings = [CourrierStatut::ENREGISTRE->value];
        if ($directionId !== null) {
            $bindings[] = $directionId;
        }
        $bindings[] = $seuil;

        $resultat = DB::selectOne(<<<SQL
            select count(*) as total
            from courriers c
            join (
                select courrier_id, max(created_at) as derniere
                from courrier_transitions
                group by courrier_id
            ) t on t.courrier_id = c.id
            where c.statut != ?
            {$conditionDirection}
            and t.derniere < ?
        SQL, $bindings);

        return (int) $resultat->total;
    }
}
