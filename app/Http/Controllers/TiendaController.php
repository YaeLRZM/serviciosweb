<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTiendaRequest;
use App\Http\Requests\UpdateTiendaRequest;
use App\Models\Tienda;

class TiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Tienda::all();
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
    public function store(StoreTiendaRequest $request)
    {
        $tienda = Tienda::create($request->validated());
        return response()->json(['message' => 'Tienda creada correctamente', 'tienda' => $tienda]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tienda $tienda)
    {
        return $tienda;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tienda $tienda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTiendaRequest $request, Tienda $tienda)
    {
        $tienda->update($request->validated());
        return response()->json(['message' => 'Tienda actualizada correctamente', 'tienda' => $tienda]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tienda $tienda)
    {
        $tienda->delete();
        return response()->json(['message' => 'Tienda eliminada correctamente']);
    }
}
