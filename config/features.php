<?php

return [
    'mock_publicaciones' => env('MOCK_PUBLICACIONES', true),
    // true = datos de sesión/mock; false = GET/PATCH /vendedores vía ApiClient
    'mock_vendedores' => env('MOCK_VENDEDORES', true),
];
