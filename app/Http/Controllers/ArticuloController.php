<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;

class ArticuloController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('verArticulos');
        return Articulo::all();
    }

    public function store(Request $request)
    {
        $this->authorize('crearArticulos');
        return Articulo::create($request->validated());
    }

    public function show(Articulo $articulo)
    {
        $this->authorize('verArticulos');
        return $articulo;
    }

    public function update(Request $request, Articulo $articulo)
    {
        $this->authorize('editarArticulos');
        $articulo->update($request->validated());
        return $articulo;
    }

    public function destroy(Articulo $articulo)
    {
        $this->authorize('eliminarArticulos');
        $articulo->delete();
        return response()->noContent();
    }
}
