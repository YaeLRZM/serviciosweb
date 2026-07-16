<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalleInventarioRequest;
use App\Http\Requests\UpdateDetalleInventarioRequest;
use App\Models\DetalleInventario;

class DetalleInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetalleInventario::all();
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
    public function store(StoreDetalleInventarioRequest $request)
    {
        $detalleInventario = DetalleInventario::create($request->validated());
        return response()->json(['message' => 'Detalle de inventario creado correctamente', 'detalleInventario' => $detalleInventario], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DetalleInventario $detalle_Inventario)
    {
        return $detalle_Inventario;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DetalleInventario $detalle_Inventario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalleInventarioRequest $request, DetalleInventario $detalle_Inventario)
    {
        $detalle_Inventario->update($request->validated());
        return response()->json(['message' => 'Detalle de inventario actualizado correctamente', 'detalleInventario' => $detalle_Inventario], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleInventario $detalle_Inventario)
    {
        $detalle_Inventario->delete();
        return response()->json(['message' => 'Detalle de inventario eliminado correctamente'], 200);
    }
}
