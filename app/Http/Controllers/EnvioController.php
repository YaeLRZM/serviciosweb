<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnvioRequest;
use App\Http\Requests\UpdateEnvioRequest;
use App\Models\Envio;

class EnvioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Envio::all();
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
    public function store(StoreEnvioRequest $request)
    {
        $envio = Envio::create($request->validated());
        return response()->json(['message' => 'Envío creado correctamente', 'envio' => $envio], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Envio $envio)
    {
        return response()->json(['envio' => $envio], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Envio $envio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnvioRequest $request, Envio $envio)
    {
        $envio->update($request->validated());
        return response()->json(['message' => 'Envío actualizado correctamente', 'envio' => $envio], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Envio $envio)
    {
        $envio->delete();
        return response()->json(['message' => 'Envío eliminado correctamente'], 200);
    }
}
