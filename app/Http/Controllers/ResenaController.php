<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResenaRequest;
use App\Http\Requests\UpdateResenaRequest;
use App\Models\Resena;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    /**
     * Listado público. Filtro opcional: ?articulo_id=
     */
    public function index(Request $request)
    {
        $query = Resena::query()->with(['user:id,nombre,email'])->latest();

        if ($request->filled('articulo_id')) {
            $query->where('articulo_id', (int) $request->input('articulo_id'));
        }

        return $query->get();
    }

    public function create()
    {
        //
    }

    /**
     * Crear reseña (JWT + permiso crearResenas).
     * user_id se toma del token; no se acepta del body.
     */
    public function store(StoreResenaRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth('api')->id();
        // Columna comentario es NOT NULL en migración.
        $data['comentario'] = $data['comentario'] ?? '';

        $resena = Resena::create($data);
        $resena->load(['user:id,nombre,email']);

        return response()->json([
            'message' => 'Reseña creada correctamente',
            'resena' => $resena,
        ], 201);
    }

    public function show(Resena $resena)
    {
        $resena->load(['user:id,nombre,email']);

        return response()->json(['resena' => $resena], 200);
    }

    public function edit(Resena $resena)
    {
        //
    }

    public function update(UpdateResenaRequest $request, Resena $resena)
    {
        $resena->update($request->validated());

        return response()->json(['message' => 'Reseña actualizada correctamente', 'resena' => $resena], 200);
    }

    public function destroy(Resena $resena)
    {
        $resena->delete();

        return response()->json(['message' => 'Reseña eliminada correctamente'], 200);
    }
}
