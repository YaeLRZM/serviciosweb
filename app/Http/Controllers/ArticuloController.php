<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticuloRequest;
use App\Http\Requests\UpdateArticuloRequest;
use App\Http\Resources\ArticuloResource;
use App\Models\Artesano;
use App\Models\Articulo;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Articulo',
    title: 'Articulo',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Huipil bordado de Oaxaca'),
        new OA\Property(property: 'talla', type: 'string', example: 'M'),
        new OA\Property(property: 'color', type: 'string', example: 'Rojo'),
        new OA\Property(property: 'bordado', type: 'string', example: 'Punto de cruz'),
        new OA\Property(property: 'tela', type: 'string', example: 'Manta'),
        new OA\Property(property: 'region', type: 'string', example: 'Oaxaca'),
        new OA\Property(
            property: 'categoria',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nombre', type: 'string', example: 'Textiles'),
                new OA\Property(property: 'descripcion', type: 'string', example: 'Prendas tejidas a mano'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'artesano',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nombre', type: 'string', example: 'María López'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'tienda',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nombre', type: 'string', example: 'Artesanías del Sur'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'imagenes',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'url', type: 'string', example: 'https://images.unsplash.com/photo-123?w=800'),
                    new OA\Property(property: 'es_principal', type: 'boolean', example: true),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class ArticuloController extends Controller
{
    #[OA\Get(
        path: '/api/articulos',
        summary: 'Listar artículos (público, sin autenticación)',
        description: 'Devuelve todos los artículos con su categoría, artesano, tienda e imágenes. '
            . 'Acepta filtros opcionales por query string; si no se envía ninguno se devuelven todos.',
        tags: ['Articulos'],
        parameters: [
            new OA\Parameter(name: 'categoria', in: 'query', required: false, description: 'ID de la categoría', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'artesano', in: 'query', required: false, description: 'ID del artesano', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tienda', in: 'query', required: false, description: 'ID de la tienda', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'color', in: 'query', required: false, description: 'Color exacto', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'bordado', in: 'query', required: false, description: 'Tipo de bordado', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tela', in: 'query', required: false, description: 'Tipo de tela', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'region', in: 'query', required: false, description: 'Región de origen', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de artículos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Articulo')
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $articulos = Articulo::with(['categoria', 'artesano', 'tienda', 'imagenes'])

            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria_id', $request->input('categoria')))
            ->when($request->filled('artesano'), fn ($q) => $q->where('artesano_id', $request->input('artesano')))
            ->when($request->filled('tienda'), fn ($q) => $q->where('tienda_id', $request->input('tienda')))

            ->when($request->filled('color'), fn ($q) => $q->where('color', $request->input('color')))
            ->when($request->filled('bordado'), fn ($q) => $q->where('bordado', $request->input('bordado')))
            ->when($request->filled('tela'), fn ($q) => $q->where('tela', $request->input('tela')))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->input('region')))
            // Catálogo público: solo publicados. Vendedor usa incluir_no_disponibles=1.
            ->when(
                ! $request->boolean('incluir_no_disponibles'),
                fn ($q) => $q->where('disponible', true)
            )
            // Búsqueda simple por texto: ?q=huipil|rebozo|tlacolula...
            ->when($request->filled('q'), function ($q) use ($request) {
                $raw = trim((string) $request->input('q'));
                if ($raw === '') {
                    return;
                }
                $term = '%'.addcslashes($raw, '%_\\').'%';
                $q->where(function ($w) use ($term) {
                    $w->where('nombre', 'ilike', $term)
                        ->orWhere('region', 'ilike', $term)
                        ->orWhere('color', 'ilike', $term)
                        ->orWhere('bordado', 'ilike', $term)
                        ->orWhere('tela', 'ilike', $term)
                        ->orWhere('descripcion', 'ilike', $term);
                });
            })
            ->get();

        return ArticuloResource::collection($articulos);
    }

    /**
     * Crear artículo (JWT + crearArticulos).
     * tienda_id se toma del vendedor autenticado (no del body).
     */
    public function store(StoreArticuloRequest $request)
    {
        $user = $request->user('api');
        $user->loadMissing('vendedor');

        $tiendaId = $user->vendedor?->tienda_id;
        if (! $tiendaId && ! $user->hasRole('admin')) {
            abort(403, 'El vendedor no tiene tienda asignada.');
        }

        // Admin sin vendedor: no prioridad móvil; exigir que exista vendedor.tienda
        // o fallar de forma clara.
        if (! $tiendaId) {
            abort(422, 'No se pudo resolver tienda_id para el usuario autenticado.');
        }

        $data = $request->validated();
        unset($data['tienda_id']); // nunca confiar en el cliente

        $data['tienda_id'] = (int) $tiendaId;
        $data['disponible'] = array_key_exists('disponible', $data)
            ? (bool) $data['disponible']
            : true;
        $data['talla'] = $data['talla'] ?? 'Único';
        $data['region'] = $data['region'] ?? 'Oaxaca';
        $data['artesano_id'] = $data['artesano_id']
            ?? Artesano::query()->orderBy('id')->value('id');

        if (! $data['artesano_id']) {
            abort(422, 'No hay artesanos en el catálogo; no se puede crear el artículo.');
        }

        $articulo = Articulo::create($data);
        $articulo->load(['categoria', 'artesano', 'tienda', 'imagenes']);

        return (new ArticuloResource($articulo))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/articulos/{id}',
        summary: 'Ver un artículo (público, sin autenticación)',
        tags: ['Articulos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del artículo', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Artículo encontrado',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Articulo')],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Artículo no encontrado'),
        ]
    )]
    public function show(Articulo $articulo)
    {
        // Ocultos (disponible=false): solo vendedor dueño de la tienda o admin.
        if (! $articulo->disponible) {
            if (! $this->puedeVerArticuloOculto($articulo)) {
                abort(404, 'Artículo no encontrado');
            }
        }

        $articulo->load(['categoria', 'artesano', 'tienda', 'imagenes']);

        return new ArticuloResource($articulo);
    }

    /**
     * JWT opcional en ruta pública: admin o vendedor de la misma tienda.
     */
    private function puedeVerArticuloOculto(Articulo $articulo): bool
    {
        $user = $this->optionalApiUser();
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $user->loadMissing('vendedor');
        $tiendaId = $user->vendedor?->tienda_id;

        return $tiendaId
            && (int) $tiendaId === (int) $articulo->tienda_id;
    }

    /**
     * Resuelve usuario JWT si viene Bearer (ruta sin middleware auth:api).
     */
    private function optionalApiUser(): ?\App\Models\User
    {
        $token = request()->bearerToken();
        if (! $token) {
            return null;
        }

        try {
            /** @var \App\Models\User|null $user */
            $user = auth('api')->setToken($token)->user();

            return $user;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/articulos/{id}',
        summary: 'Actualizar un artículo (requiere permiso editarArticulos)',
        security: [['bearerAuth' => []]],
        tags: ['Articulos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'precio', 'stock'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Huipil bordado'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Bordado a mano'),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', example: 1250.00),
                    new OA\Property(property: 'stock', type: 'integer', example: 12),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Artículo actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Articulo')),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function update(UpdateArticuloRequest $request, Articulo $articulo)
    {
        $articulo->update($request->validated());
        $articulo->load(['categoria', 'artesano', 'tienda', 'imagenes']);

        return new ArticuloResource($articulo);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/articulos/{id}',
        summary: 'Eliminar un artículo (requiere permiso eliminarArticulos)',
        security: [['bearerAuth' => []]],
        tags: ['Articulos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artículo eliminado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function destroy(Articulo $articulo)
    {
        $user = request()->user('api');
        abort_unless(
            $user?->hasPermissionTo('eliminarArticulos', 'web'),
            403,
            'No autorizado'
        );

        // Ownership: admin puede todo; vendedor solo artículos de su tienda
        // (misma lógica de seguridad que UpdateArticuloRequest).
        if (! $user->hasRole('admin')) {
            $user->loadMissing('vendedor');
            $tiendaId = $user->vendedor?->tienda_id;
            abort_unless(
                $tiendaId && (int) $tiendaId === (int) $articulo->tienda_id,
                403,
                'No autorizado'
            );
        }

        $articulo->delete();

        return response()->json(['message' => 'Artículo eliminado correctamente']);
    }
}
