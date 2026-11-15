<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Orígenes locales + los que pongas en el .env del servidor:
    // CORS_ALLOWED_ORIGINS=https://mi-admin.com,https://mi-app.com
    'allowed_origins' => array_values(array_filter(array_merge(
        [
            'http://localhost:8080',   // Vue (admin)
            'http://127.0.0.1:8080',
            'http://localhost:8100',   // Ionic
            'http://127.0.0.1:8100',
            'capacitor://localhost',   // Ionic empaquetada (iOS)
            'http://localhost',        // Ionic empaquetada (Android)
        ],
        array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))
    ))),

    'allowed_origins_patterns' => ['#^http://(192\.168|10\.\d{1,3}|172\.(1[6-9]|2\d|3[01]))\.\d{1,3}\.\d{1,3}:(8080|8100)$#',],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
