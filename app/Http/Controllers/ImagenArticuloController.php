<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImagenArticuloRequest;
use App\Http\Requests\UpdateImagenArticuloRequest;
use App\Models\ImagenArticulo;

class ImagenArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ImagenArticulo::all();
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
    public function store(StoreImagenArticuloRequest $request)
    {
        $imagenArticulo = ImagenArticulo::create($request->validated());
        return response()->json(['message' => 'Imagen de artículo creada correctamente', 'imagenArticulo' => $imagenArticulo], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ImagenArticulo $imagenArticulo)
    {
        return response()->json(['imagenArticulo' => $imagenArticulo], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImagenArticulo $imagenArticulo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImagenArticuloRequest $request, ImagenArticulo $imagenArticulo)
    {
        $imagenArticulo->update($request->validated());
        return response()->json(['message' => 'Imagen de artículo actualizada correctamente', 'imagenArticulo' => $imagenArticulo], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImagenArticulo $imagenArticulo)
    {
        $imagenArticulo->delete();
        return response()->json(['message' => 'Imagen de artículo eliminada correctamente'], 200);
    }
}
