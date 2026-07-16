<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalleVentaRequest;
use App\Http\Requests\UpdateDetalleVentaRequest;
use App\Models\DetalleVenta;

class DetalleVentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetalleVenta::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDetalleVentaRequest $request)
    {
        $venta = DetalleVenta::create($request->validated());
        return response()->json(['message' => 'Detalle de venta creado correctamente', 'detalleVenta' => $venta], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DetalleVenta $detalle_venta)
    {
        return response()->json(['detalleVenta' => $detalle_venta], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalleVentaRequest $request, DetalleVenta $detalle_venta)
    {
        $detalle_venta->update($request->validated());
        return response()->json(['message' => 'Detalle de venta actualizado correctamente', 'detalleVenta' => $detalle_venta], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleVenta $detalle_venta)
    {
        return response()->json(['message' => 'Eliminación de detalle de venta no permitida'], 403);
    }
}
