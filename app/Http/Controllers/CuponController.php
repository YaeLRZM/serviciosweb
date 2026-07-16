<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuponRequest;
use App\Http\Requests\UpdateCuponRequest;
use App\Models\Cupon;

class CuponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Cupon::all();
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
    public function store(StoreCuponRequest $request)
    {
        $cupon = Cupon::create($request->validated());
        return response()->json(['message' => 'Cupón creado correctamente', 'cupon' => $cupon], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cupon $cupon)
    {
        return response()->json(['cupon' => $cupon], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cupon $cupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCuponRequest $request, Cupon $cupon)
    {
        $cupon->update($request->validated());
        return response()->json(['message' => 'Cupón actualizado correctamente', 'cupon' => $cupon], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cupon $cupon)
    {
        $cupon->delete();
        return response()->json(['message' => 'Cupón eliminado correctamente'], 200);
    }
}
