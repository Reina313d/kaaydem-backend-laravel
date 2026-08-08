<?php

return [

    // Chemins concernes par la politique CORS : toute l'API + l'endpoint CSRF de Sanctum.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Origines autorisees a consommer l'API depuis le navigateur (le frontend Vite).
    // Configurable via CORS_ALLOWED_ORIGINS dans .env (liste separee par des virgules) ;
    // par defaut, les ports habituels de Vite en developpement.
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', env(
            'FRONTEND_URL',
            'http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173,http://127.0.0.1:4173'
        )))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // false : l'authentification se fait par token Bearer (Sanctum tokens), pas par cookies.
    'supports_credentials' => false,

];
