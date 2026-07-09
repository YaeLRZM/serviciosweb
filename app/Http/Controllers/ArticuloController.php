<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyArticuloRequest;
use App\Http\Requests\IndexArticuloRequest;
use App\Http\Requests\ShowArticuloRequest;
use App\Http\Requests\StoreArticuloRequest;
use App\Http\Requests\UpdateArticuloRequest;
use App\Models\Articulo;

class ArticuloController extends Controller
{
    public function index(IndexArticuloRequest $request)
    {
        return Articulo::all();
    }

    public function store(StoreArticuloRequest $request)
    {
        return Articulo::create($request->validated());
    }

    public function show(ShowArticuloRequest $request, Articulo $articulo)
    {
        return $articulo;
    }

    public function update(UpdateArticuloRequest $request, Articulo $articulo)
    {
        $articulo->update($request->validated());

        return $articulo;
    }

    public function destroy(DestroyArticuloRequest $request, Articulo $articulo)
    {
        $articulo->delete();

        return response()->noContent();
    }
}
