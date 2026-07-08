<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticuloRequest;
use App\Http\Requests\UpdateArticuloRequest;
use App\Models\Articulo;

class ArticuloController extends Controller
{
    public function create()
    {
        $usuarioActivo = auth()->user();
        if ($usuarioActivo && $usuarioActivo->can('crearArticulos')) {
            return response()->json([
                'message' => 'Formulario para crear artículos',
            ]);
        }

        return response()->json(['message' => 'No tienes permiso para crear artículos.'], 403);
    }

    public function index()
    {
        // $usuarioActivo = auth()->user();
        // if ($usuarioActivo && $usuarioActivo->can('verArticulos')) {
        //     }
        //     return response()->json(['message' => 'No tienes permiso para ver los artículos.'], 403);
        return Articulo::all();
    }

    public function store(StoreArticuloRequest $request)
    {
        $usuarioActivo = auth()->user();
        if ($usuarioActivo && $usuarioActivo->can('crearArticulos')) {
            return Articulo::create($request->validated());
        }
        return response()->json(['message' => 'No tienes permiso para crear artículos.'], 403);
    }

    public function show(Articulo $articulo)
    {
        if (!auth()->user()->can('verArticulos')) {
            return response()->json(['message' => 'No tienes permiso para ver los artículos.'], 403);
        }
        return $articulo;
    }

    public function update(UpdateArticuloRequest $request, Articulo $articulo)
    {
        if (!auth()->user()->can('editarArticulos')) {
            return response()->json(['message' => 'No tienes permiso para editar artículos.'], 403);
        }
        $articulo->update($request->validated());
        return $articulo;
    }

    public function destroy(Articulo $articulo)
    {
        if (!auth()->user()->can('eliminarArticulos')) {
            return response()->json(['message' => 'No tienes permiso para eliminar artículos.'], 403);
        }
        $articulo->delete();
        return response()->noContent();
    }
}
