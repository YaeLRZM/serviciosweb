<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;

class ArticuloController extends Controller
{
    public function index()
    {
        $usuarioActivo = auth()->user();
        if ($usuarioActivo && $usuarioActivo->can('verArticulos')) {
            return Articulo::all();
        }
        return response()->json(['message' => 'No tienes permiso para ver los artículos.'], 403);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('crearArticulos')) {
            return response()->json(['message' => 'No tienes permiso para crear artículos.'], 403);
        }
        return Articulo::create($request->validated());
    }

    public function show(Articulo $articulo)
    {
        if (!auth()->user()->can('verArticulos')) {
            return response()->json(['message' => 'No tienes permiso para ver los artículos.'], 403);
        }
        return $articulo;
    }

    public function update(Request $request, Articulo $articulo)
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
