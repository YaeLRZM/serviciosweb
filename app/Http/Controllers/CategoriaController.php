<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Categoria',
    title: 'Categoria',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Textiles'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Prendas tejidas a mano'),
    ],
    type: 'object'
)]
class CategoriaController extends Controller
{
    #[OA\Get(
        path: '/api/categorias',
        summary: 'Listar categorías (público)',
        tags: ['Categorias'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de categorías',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Categoria'))
            ),
        ]
    )]
    public function index()
    {
        // Catálogo público: solo categorías visibles (prendas/textiles sembradas).
        return Categoria::query()
            ->where('visible', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion', 'imagen', 'visible']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());
        return response()->json(['message' => 'Categoria creada correctamente', 'categoria' => $categoria], 201);
    }

    #[OA\Get(
        path: '/api/categorias/{id}',
        summary: 'Ver una categoría (público)',
        tags: ['Categorias'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categoría encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Categoria')),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function show(Categoria $categoria)
    {
        return $categoria;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());
        return response()->json(['message' => 'Categoria actualizada correctamente', 'categoria' => $categoria]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        abort_unless(request()->user('api')?->hasPermissionTo('eliminarCategorias', 'web'), 403, 'No autorizado');

        $categoria->delete();
        return response()->json(['message' => 'Categoria eliminada correctamente']);
    }
}
