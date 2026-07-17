<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí configuras el intercambio de recursos entre orígenes (CORS).
    | Determina qué operaciones cross-origin pueden ejecutarse desde el
    | navegador. El frontend en client/ corre en un puerto distinto al de
    | Laravel (php artisan serve -> :8000), por eso el navegador exige CORS.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'chatbot'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',   // frontend Express de client/
        'http://127.0.0.1:3000',
        'http://localhost:5173',   // por si usas Vite en el futuro
        'http://127.0.0.1:5173',
        'http://localhost:8000',   // Swagger UI "Try it out" (misma API)
        'http://127.0.0.1:8000',
    ],

    'allowed_origins_patterns' => [],

    // 4. Cabeceras de request permitidas (incluye Authorization para el JWT).
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 5. true si el frontend envía cookies o el token JWT (Authorization).
    //    Nota: con esto en true, allowed_origins NO puede ser ['*'],
    //    deben ser orígenes explícitos como los de arriba.
    'supports_credentials' => true,

];
