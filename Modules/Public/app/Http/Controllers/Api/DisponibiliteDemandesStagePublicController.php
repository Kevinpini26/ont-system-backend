<?php

namespace Modules\Public\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Stagiaires\Models\DisponibiliteDemandesStage;

/**
 * Lecture seule, sans authentification : consultée par la page d'accueil et
 * par /demande-de-stage pour savoir quels types de stage accepter des
 * candidatures. Le rejet réel d'un dépôt sur un type fermé est appliqué
 * côté serveur par DeposerDemandeStageRequest, pas seulement ici.
 */
class DisponibiliteDemandesStagePublicController extends Controller
{
    public function show()
    {
        $disponibilite = DisponibiliteDemandesStage::actuelle();

        return response()->json([
            'academique' => $disponibilite->academique_ouvert,
            'professionnel' => $disponibilite->professionnel_ouvert,
        ]);
    }
}
