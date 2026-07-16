<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendedorRequest;
use App\Http\Requests\UpdateVendedorRequest;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Vendedor::all();
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
    public function store(StoreVendedorRequest $request)
    {
        $vendedor = Vendedor::create($request->validated());
        return response()->json(['message' => 'Vendedor creado correctamente', 'vendedor' => $vendedor], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendedor $vendedor)
    {
        return $vendedor;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendedor $vendedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendedorRequest $request, Vendedor $vendedor)
    {
        $vendedor->update($request->validated());
        return response()->json(['message' => 'Vendedor actualizado correctamente', 'vendedor' => $vendedor], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendedor $vendedor)
    {
        $vendedor->delete();
        return response()->json(['message' => 'Vendedor eliminado correctamente'], 200);
    }
}
