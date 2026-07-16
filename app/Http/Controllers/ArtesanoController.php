<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtesanoRequest;
use App\Http\Requests\UpdateArtesanoRequest;
use App\Models\Artesano;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Artesano',
    title: 'Artesano',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'María López'),
    ],
    type: 'object'
)]
class ArtesanoController extends Controller
{
    #[OA\Get(
        path: '/api/artesanos',
        summary: 'Listar artesanos (público)',
        tags: ['Artesanos'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de artesanos',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Artesano'))
            ),
        ]
    )]
    public function index()
    {
        return Artesano::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArtesanoRequest $request)
    {
        $artesano = Artesano::create($request->validated());
        return response()->json(['message' => 'Artesano creado correctamente', 'artesano' => $artesano], 201);
    }

    #[OA\Get(
        path: '/api/artesanos/{id}',
        summary: 'Ver un artesano (público)',
        tags: ['Artesanos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artesano encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Artesano')),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(Artesano $artesano)
    {
        return $artesano;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArtesanoRequest $request, Artesano $artesano)
    {
        $artesano->update($request->validated());
        return response()->json(['message' => 'Artesano actualizado correctamente', 'artesano' => $artesano]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Artesano $artesano)
    {
        abort_unless(request()->user('api')?->hasPermissionTo('eliminarArtesanos', 'web'), 403, 'No autorizado');

        $artesano->delete();
        return response()->json(['message' => 'Artesano eliminado correctamente']);
    }
}
