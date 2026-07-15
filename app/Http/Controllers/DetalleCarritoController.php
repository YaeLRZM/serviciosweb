<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalle_CarritoRequest;
use App\Http\Requests\UpdateDetalle_CarritoRequest;
use App\Models\DetalleCarrito;

class DetalleCarritoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetalleCarrito::all();
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
    public function store(StoreDetalle_CarritoRequest $request)
    {
        return DetalleCarrito::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(DetalleCarrito $detalleCarrito)
    {
        return $detalleCarrito;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DetalleCarrito $detalleCarrito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalle_CarritoRequest $request, DetalleCarrito $detalleCarrito)
    {
        return $detalleCarrito->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleCarrito $detalleCarrito)
    {
        return $detalleCarrito->delete();
    }
}
