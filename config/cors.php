<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | L'API est consommée exclusivement par le frontend ONT (SPA React).
    | Aucun autre domaine n'est autorisé : FRONTEND_URL doit être défini
    | explicitement en production (pas de wildcard "*").
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Authentification par jeton Sanctum transmis dans l'en-tête
    // Authorization (pas de cookie de session) : les credentials CORS ne
    // sont pas nécessaires.
    'supports_credentials' => false,

];
