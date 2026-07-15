<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventarioRequest;
use App\Http\Requests\UpdateInventarioRequest;
use App\Models\Inventario;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inventario::all();
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
    public function store(StoreInventarioRequest $request)
    {
        $inventario = Inventario::create($request->all());
        return response()->json(['message' => 'Inventario creado correctamente', 'inventario' => $inventario], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventario $inventario)
    {
        return $inventario;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventario $inventario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventarioRequest $request, Inventario $inventario)
    {
        $inventario->update($request->all());
        return response()->json(['message' => 'Inventario actualizado correctamente', 'inventario' => $inventario], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        return response()->json(['message' => 'Inventario eliminado correctamente'], 200);
    }
}
