<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCupon_CanjeadoRequest;
use App\Http\Requests\UpdateCupon_CanjeadoRequest;
use App\Models\CuponCanjeado;

class CuponCanjeadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CuponCanjeado::all();
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
    public function store(StoreCupon_CanjeadoRequest $request)
    {
        $cuponCanjeado = CuponCanjeado::create($request->validated());
        return response()->json(['message' => 'Cupón canjeado creado correctamente', 'cuponCanjeado' => $cuponCanjeado], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CuponCanjeado $cuponCanjeado)
    {
        return response()->json(['cuponCanjeado' => $cuponCanjeado], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CuponCanjeado $cuponCanjeado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCupon_CanjeadoRequest $request, CuponCanjeado $cuponCanjeado)
    {
        $cuponCanjeado->update($request->validated());
        return response()->json(['message' => 'Cupón canjeado actualizado correctamente', 'cuponCanjeado' => $cuponCanjeado], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CuponCanjeado $cuponCanjeado)
    {
        $cuponCanjeado->delete();
        return response()->json(['message' => 'Cupón canjeado eliminado correctamente'], 200);
    }
}
