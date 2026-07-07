<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Models\Compra;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Compra::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Compra::create($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompraRequest $request)
    {
        return Compra::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        return $compra;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        return $compra;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        $compra->update($request->validated());
        return $compra;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {
        $resena->delete();
        return response()->noContent();
    }
}
