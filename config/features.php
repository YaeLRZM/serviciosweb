<?php

return [
    'mock_publicaciones' => env('MOCK_PUBLICACIONES', true),
    // true = datos de sesión/mock; false = GET/PATCH /vendedores vía ApiClient
    'mock_vendedores' => env('MOCK_VENDEDORES', true),
    // true = datos de sesión/mock; false = users + roles Spatie reales en BD
    'mock_usuarios' => env('MOCK_USUARIOS', true),
    // true = datos de sesión/mock; false = Categoria real en BD
    'mock_categorias' => env('MOCK_CATEGORIAS', true),
    // true = datos de sesión/mock; false = Artesano real en BD
    'mock_artesanos' => env('MOCK_ARTESANOS', true),
];
