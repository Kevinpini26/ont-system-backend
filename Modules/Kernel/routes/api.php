<?php

use Illuminate\Support\Facades\Route;
use Modules\Kernel\Http\Controllers\Api\AgentCircuitCourrierController;
use Modules\Kernel\Http\Controllers\Api\AuditLogController;
use Modules\Kernel\Http\Controllers\Api\AuthController;
use Modules\Kernel\Http\Controllers\Api\DgDisponibiliteController;
use Modules\Kernel\Http\Controllers\Api\DirectionController;
use Modules\Kernel\Http\Controllers\Api\NotificationCompteurController;
use Modules\Kernel\Http\Controllers\Api\NotificationController;
use Modules\Kernel\Http\Controllers\Api\RapportPeriodiqueController;
use Modules\Kernel\Http\Controllers\Api\UserController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/marquer-toutes-lues', [NotificationController::class, 'marquerToutesLues']);
        Route::post('/notifications/{id}/marquer-lu', [NotificationController::class, 'marquerLu']);

        Route::get('/notifications/compteurs', [NotificationCompteurController::class, 'index']);
        Route::post('/notifications/marquer-consulte', [NotificationCompteurController::class, 'marquerConsulte']);

        Route::get('/agents-circuit-courrier', [AgentCircuitCourrierController::class, 'index']);

        Route::get('/directions', [DirectionController::class, 'index']);
        Route::get('/directions/{direction}', [DirectionController::class, 'show']);

        Route::get('/dg-disponibilite', [DgDisponibiliteController::class, 'show']);
        Route::post('/dg-disponibilite', [DgDisponibiliteController::class, 'update']);

        // Hors du groupe role:administrateur ci-dessous : accessible aussi à
        // la DFP, voir Gate::genererRapportTutelle (seul vrai contrôle
        // d'accès de cette route).
        Route::get('/rapports/periodique', [RapportPeriodiqueController::class, 'genererPdf']);

        Route::middleware('role:administrateur')->group(function () {
            Route::post('/directions', [DirectionController::class, 'store']);
            Route::put('/directions/{direction}', [DirectionController::class, 'update']);
            Route::patch('/directions/{direction}', [DirectionController::class, 'update']);
            Route::delete('/directions/{direction}', [DirectionController::class, 'destroy']);

            Route::apiResource('users', UserController::class);
            Route::delete('/users/{user}/tokens', [UserController::class, 'revoquerJetons']);

            Route::get('/audit-logs', [AuditLogController::class, 'index']);
        });
    });
});
