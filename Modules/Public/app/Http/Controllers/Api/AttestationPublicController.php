<?php

namespace Modules\Public\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Public\Http\Resources\AttestationPublicResource;
use Modules\Stagiaires\Models\Stagiaire;

class AttestationPublicController extends Controller
{
    /**
     * Vérification de l'authenticité d'une attestation de stage à partir de
     * son numéro (typiquement via le QR code imprimé sur le document).
     * Endpoint public : aucune authentification requise.
     */
    public function show(string $numeroAttestation): JsonResponse
    {
        $stagiaire = Stagiaire::query()
            ->where('numero_attestation', $numeroAttestation)
            ->first();

        if (! $stagiaire) {
            return response()->json(['message' => 'Aucune attestation ne correspond à ce numéro.'], 404);
        }

        return (new AttestationPublicResource($stagiaire))->response();
    }
}
