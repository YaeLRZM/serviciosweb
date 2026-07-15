<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'API Servicios Web - Artesanías',
    description: 'API REST para el catálogo de artículos artesanales, tiendas, categorías y artesanos.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}
