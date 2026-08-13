<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\Api\AttestationPublicController;
use Modules\Public\Http\Controllers\Api\CourrierExternePublicController;
use Modules\Public\Http\Controllers\Api\DemandeStagePublicController;
use Modules\Public\Http\Controllers\Api\DisponibiliteDemandesStagePublicController;
use Modules\Public\Http\Controllers\Api\DossierPublicController;
use Modules\Public\Http\Controllers\Api\LienPublicController;

// Aucune authentification : accessible à tout candidat externe muni de son
// numéro d'accusé de réception, ou d'un lien à usage unique.
Route::prefix('v1/public')->middleware('throttle:sensitive')->group(function () {
    Route::post('/demandes-stage', [DemandeStagePublicController::class, 'store']);
    Route::get('/disponibilite-demandes-stage', [DisponibiliteDemandesStagePublicController::class, 'show']);
    Route::post('/courriers-externes', [CourrierExternePublicController::class, 'store']);
    Route::get('/dossiers/{numeroAccuseReception}', [DossierPublicController::class, 'show']);
    Route::get('/attestations/{numeroAttestation}', [AttestationPublicController::class, 'show']);

    Route::get('/liens/{token}', [LienPublicController::class, 'show']);
    Route::get('/liens/{token}/convention.pdf', [LienPublicController::class, 'telechargerConvention']);
    Route::post('/liens/{token}/signer-convention', [LienPublicController::class, 'signerConvention']);
    Route::post('/liens/{token}/retour', [LienPublicController::class, 'soumettreRetour']);
});
