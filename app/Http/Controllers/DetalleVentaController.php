<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalle_VentaRequest;
use App\Http\Requests\UpdateDetalle_VentaRequest;
use App\Models\Detalle_Venta;

class DetalleVentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Detalle_Venta::all();
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
    public function store(StoreDetalle_VentaRequest $request)
    {
        $venta = Detalle_Venta::create($request->validated());
        return response()->json(['message' => 'Detalle de venta creado correctamente', 'detalleVenta' => $venta], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Detalle_Venta $detalle_Venta)
    {
        return response()->json(['detalleVenta' => $detalle_Venta], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Detalle_Venta $detalle_Venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalle_VentaRequest $request, Detalle_Venta $detalle_Venta)
    {
        $detalle_Venta->update($request->validated());
        return response()->json(['message' => 'Detalle de venta actualizado correctamente', 'detalleVenta' => $detalle_Venta], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detalle_Venta $detalle_Venta)
    {
        return response()->json(['message' => 'Eliminación de detalle de venta no permitida'], 403);
    }
}
