<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEstadoRequest;
use App\Http\Requests\UpdateEstadoRequest;
use App\Models\Estado;

class EstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Estado::all();
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
    public function store(StoreEstadoRequest $request)
    {
        $estado = Estado::create($request->validated());
        return response()->json(['message' => 'Estado creado correctamente', 'estado' => $estado]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Estado $estado)
    {
        return $estado;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estado $estado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEstadoRequest $request, Estado $estado)
    {
        $estado->update($request->validated());
        return response()->json(['message' => 'Estado actualizado correctamente', 'estado' => $estado]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estado $estado)
    {
        $estado->delete();
        return response()->json(['message' => 'Estado eliminado correctamente']);
    }
}
