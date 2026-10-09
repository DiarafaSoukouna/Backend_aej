<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agence Emploi Jeunes API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Agence Emploi Jeunes external API integration
    |
    */

    'base_url' => env('AEJ_API_BASE_URL', 'https://agenceemploijeunes.ci/api/v1.0'),
    'backoffice_url' => env('AEJ_BACKOFFICE_URL', 'http://aejplateformetest.westeurope.cloudapp.azure.com/projets/api/v1/backoffice'),
    'api_key' => env('AEJ_API_KEY', null),
    'backoffice_email' => env('AEJ_BACKOFFICE_EMAIL', 'user-mail'),
    'backoffice_password' => env('AEJ_BACKOFFICE_PASSWORD', 'user-password'),
    'backoffice_device_name' => env('AEJ_BACKOFFICE_DEVICE_NAME', 'test-warp'),
    'timeout' => env('AEJ_API_TIMEOUT', 30),
    'retry' => env('AEJ_API_RETRY', 3),
    'retry_delay' => env('AEJ_API_RETRY_DELAY', 100),

    'endpoints' => [
        'types_pieces_identites' => '/types-pieces-identites',
        'situations_matrimoniale' => '/situations-matrimoniale',
        'situations_handicaps' => '/situations-handicaps',
        'secteurs' => '/secteurs',
        'sous_secteurs' => '/sous-secteurs',
        'niveaux_etudes' => '/niveaux-etudes',
        'agences_regionales' => '/list-agence-regionale',
        'projet_parameters' => '/load-projet-parameter',
        'lieu_habitations' => '/lieu-habitations',
        'sexes' => '/sexes',
        'pays' => '/pays',
    ],

    'backoffice_endpoints' => [
        'auth_login' => '/auth/login',
        'micro_projets' => '/projets',
    ],
    
    'cache' => [
        'enabled' => env('AEJ_API_CACHE_ENABLED', true),
        'ttl' => env('AEJ_API_CACHE_TTL', 86400), // 24 hours
        'prefix' => 'aej_api:',
    ],
    
    'sync' => [
        'enabled' => env('AEJ_API_SYNC_ENABLED', true),
        'queue' => env('AEJ_API_SYNC_QUEUE', 'default'),
    ],
];
