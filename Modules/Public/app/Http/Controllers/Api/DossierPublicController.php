<?php

namespace Modules\Public\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Courrier\Models\Courrier;
use Modules\Public\Http\Resources\DossierPublicResource;
use Modules\Stagiaires\Models\Stagiaire;

class DossierPublicController extends Controller
{
    /**
     * Vérification du statut d'un dossier par un candidat externe, à partir
     * du numéro d'accusé de réception reçu à la remise du courrier.
     * Endpoint public : aucune authentification requise.
     */
    public function show(string $numeroAccuseReception): JsonResponse
    {
        $courrier = Courrier::query()
            ->where('numero_accuse_reception', $numeroAccuseReception)
            ->first();

        if (! $courrier) {
            return response()->json(['message' => 'Aucun dossier ne correspond à ce numéro.'], 404);
        }

        $stagiaire = Stagiaire::query()->where('courrier_id', $courrier->id)->first();

        return (new DossierPublicResource($courrier, $stagiaire))->response();
    }
}
