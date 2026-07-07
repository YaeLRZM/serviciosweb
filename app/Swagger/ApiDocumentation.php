<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Servicios Web API',
    version: '1.0.0',
    description: 'Documentación Swagger de la API con autenticación JWT.'
)]
#[OA\Server(
    url: 'http://localhost/api',
    description: 'Servidor local de la API'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'usuario@correo.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
    ]
)]
#[OA\Schema(
    schema: 'TokenResponse',
    properties: [
        new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
        new OA\Property(
            property: 'user',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Admin'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@correo.com'),
                new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-07-06T12:00:00Z'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Admin'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@correo.com'),
        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Articulo',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Resena',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Compra',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class ApiDocumentation
{
    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        summary: 'Iniciar sesión y obtener JWT',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token JWT generado',
                content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')
            ),
            new OA\Response(response: 401, description: 'Credenciales inválidas'),
        ]
    )]
    public function login(): void
    {
    }

    #[OA\Get(
        path: '/api/me',
        tags: ['Auth'],
        summary: 'Obtener el usuario autenticado',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function me(): void
    {
    }

    #[OA\Post(
        path: '/api/logout',
        tags: ['Auth'],
        summary: 'Cerrar sesión y revocar el token',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Sesión cerrada'),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function logout(): void
    {
    }

    #[OA\Post(
        path: '/api/refresh',
        tags: ['Auth'],
        summary: 'Renovar el JWT',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token renovado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function refresh(): void
    {
    }

    #[OA\Get(
        path: '/api/articulos',
        tags: ['Articulos'],
        summary: 'Listar artículos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de artículos',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Articulo'))
            ),
        ]
    )]
    public function articulosIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/articulos',
        tags: ['Articulos'],
        summary: 'Crear artículo',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Artículo creado',
                content: new OA\JsonContent(ref: '#/components/schemas/Articulo')
            ),
        ]
    )]
    public function articulosStore(): void
    {
    }

    #[OA\Get(
        path: '/api/articulos/{articulo}',
        tags: ['Articulos'],
        summary: 'Obtener un artículo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'articulo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Artículo',
                content: new OA\JsonContent(ref: '#/components/schemas/Articulo')
            ),
        ]
    )]
    public function articulosShow(): void
    {
    }

    #[OA\Put(
        path: '/api/articulos/{articulo}',
        tags: ['Articulos'],
        summary: 'Actualizar artículo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'articulo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Artículo actualizado',
                content: new OA\JsonContent(ref: '#/components/schemas/Articulo')
            ),
        ]
    )]
    public function articulosUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/articulos/{articulo}',
        tags: ['Articulos'],
        summary: 'Eliminar artículo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'articulo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Artículo eliminado'),
        ]
    )]
    public function articulosDestroy(): void
    {
    }

    #[OA\Get(
        path: '/api/resenas',
        tags: ['Resenas'],
        summary: 'Listar reseñas',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de reseñas',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Resena'))
            ),
        ]
    )]
    public function resenasIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/resenas',
        tags: ['Resenas'],
        summary: 'Crear reseña',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reseña creada',
                content: new OA\JsonContent(ref: '#/components/schemas/Resena')
            ),
        ]
    )]
    public function resenasStore(): void
    {
    }

    #[OA\Get(
        path: '/api/resenas/{resena}',
        tags: ['Resenas'],
        summary: 'Obtener una reseña',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'resena', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reseña',
                content: new OA\JsonContent(ref: '#/components/schemas/Resena')
            ),
        ]
    )]
    public function resenasShow(): void
    {
    }

    #[OA\Put(
        path: '/api/resenas/{resena}',
        tags: ['Resenas'],
        summary: 'Actualizar reseña',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'resena', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reseña actualizada',
                content: new OA\JsonContent(ref: '#/components/schemas/Resena')
            ),
        ]
    )]
    public function resenasUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/resenas/{resena}',
        tags: ['Resenas'],
        summary: 'Eliminar reseña',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'resena', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Reseña eliminada'),
        ]
    )]
    public function resenasDestroy(): void
    {
    }

    #[OA\Get(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Listar usuarios',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de usuarios',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))
            ),
        ]
    )]
    public function usuariosIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Crear usuario',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario creado',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
        ]
    )]
    public function usuariosStore(): void
    {
    }

    #[OA\Get(
        path: '/api/usuarios/{usuario}',
        tags: ['Usuarios'],
        summary: 'Obtener un usuario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
        ]
    )]
    public function usuariosShow(): void
    {
    }

    #[OA\Put(
        path: '/api/usuarios/{usuario}',
        tags: ['Usuarios'],
        summary: 'Actualizar usuario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario actualizado',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
        ]
    )]
    public function usuariosUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/usuarios/{usuario}',
        tags: ['Usuarios'],
        summary: 'Eliminar usuario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usuario eliminado'),
        ]
    )]
    public function usuariosDestroy(): void
    {
    }

    #[OA\Get(
        path: '/api/compras',
        tags: ['Compras'],
        summary: 'Listar compras',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de compras',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Compra'))
            ),
        ]
    )]
    public function comprasIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/compras',
        tags: ['Compras'],
        summary: 'Crear compra',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compra creada',
                content: new OA\JsonContent(ref: '#/components/schemas/Compra')
            ),
        ]
    )]
    public function comprasStore(): void
    {
    }

    #[OA\Get(
        path: '/api/compras/{compra}',
        tags: ['Compras'],
        summary: 'Obtener una compra',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'compra', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compra',
                content: new OA\JsonContent(ref: '#/components/schemas/Compra')
            ),
        ]
    )]
    public function comprasShow(): void
    {
    }

    #[OA\Put(
        path: '/api/compras/{compra}',
        tags: ['Compras'],
        summary: 'Actualizar compra',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'compra', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compra actualizada',
                content: new OA\JsonContent(ref: '#/components/schemas/Compra')
            ),
        ]
    )]
    public function comprasUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/compras/{compra}',
        tags: ['Compras'],
        summary: 'Eliminar compra',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'compra', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Compra eliminada'),
        ]
    )]
    public function comprasDestroy(): void
    {
    }
}
