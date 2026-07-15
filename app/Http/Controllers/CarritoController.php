<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarritoRequest;
use App\Http\Requests\UpdateCarritoRequest;
use App\Models\Carrito;

class CarritoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Carrito::all();
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
    public function store(StoreCarritoRequest $request)
    {
        $carrito = Carrito::create($request->validated());
        return response()->json(['message' => 'Carrito creado correctamente', 'carrito' => $carrito], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carrito $carrito)
    {
        return response()->json(['carrito' => $carrito], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carrito $carrito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCarritoRequest $request, Carrito $carrito)
    {
        $carrito->update($request->validated());
        return response()->json(['message' => 'Carrito actualizado correctamente', 'carrito' => $carrito], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carrito $carrito)
    {
        $carrito->delete();
        return response()->json(['message' => 'Carrito eliminado correctamente'], 200);
    }
}
