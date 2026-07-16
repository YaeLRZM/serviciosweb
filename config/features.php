<?php

return [
    'mock_publicaciones' => env('MOCK_PUBLICACIONES', true),
    // Histórico: mock de vendedores. El admin ya usa Eloquent (vendedors). Flag sin efecto.
    'mock_vendedores' => env('MOCK_VENDEDORES', false),
];
