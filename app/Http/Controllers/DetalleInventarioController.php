<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalle_InventarioRequest;
use App\Http\Requests\UpdateDetalle_InventarioRequest;
use App\Models\Detalle_Inventario;

class DetalleInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Detalle_Inventario::all();
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
    public function store(StoreDetalle_InventarioRequest $request)
    {
        $detalleInventario = Detalle_Inventario::create($request->validated());
        return response()->json(['message' => 'Detalle de inventario creado correctamente', 'detalleInventario' => $detalleInventario], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Detalle_Inventario $detalle_Inventario)
    {
        return $detalle_Inventario;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Detalle_Inventario $detalle_Inventario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalle_InventarioRequest $request, Detalle_Inventario $detalle_Inventario)
    {
        $detalle_Inventario->update($request->validated());
        return response()->json(['message' => 'Detalle de inventario actualizado correctamente', 'detalleInventario' => $detalle_Inventario], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detalle_Inventario $detalle_Inventario)
    {
        $detalle_Inventario->delete();
        return response()->json(['message' => 'Detalle de inventario eliminado correctamente'], 200);
    }
}
