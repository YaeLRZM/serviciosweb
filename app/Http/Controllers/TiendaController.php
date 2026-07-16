<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTiendaRequest;
use App\Http\Requests\UpdateTiendaRequest;
use App\Http\Resources\TiendaResource;
use App\Models\Tienda;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Tienda',
    title: 'Tienda',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Artesanías del Sur'),
    ],
    type: 'object'
)]
class TiendaController extends Controller
{
    #[OA\Get(
        path: '/api/tiendas',
        summary: 'Listar tiendas (público)',
        tags: ['Tiendas'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de tiendas',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Tienda'))
            ),
        ]
    )]
    public function index()
    {
        return TiendaResource::collection(Tienda::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTiendaRequest $request)
    {
        $tienda = Tienda::create($request->validated());
        return response()->json(['message' => 'Tienda creada correctamente', 'tienda' => $tienda], 201);
    }

    #[OA\Get(
        path: '/api/tiendas/{id}',
        summary: 'Ver una tienda (público)',
        tags: ['Tiendas'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tienda encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Tienda')),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function show(Tienda $tienda)
    {
        return new TiendaResource($tienda);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTiendaRequest $request, Tienda $tienda)
    {
        $tienda->update($request->validated());
        return response()->json(['message' => 'Tienda actualizada correctamente', 'tienda' => $tienda]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tienda $tienda)
    {
        abort_unless(request()->user('api')?->hasPermissionTo('eliminarTiendas', 'web'), 403, 'No autorizado');

        $tienda->delete();
        return response()->json(['message' => 'Tienda eliminada correctamente']);
    }
}
