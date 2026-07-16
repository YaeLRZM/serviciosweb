<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampanaRequest;
use App\Http\Requests\UpdateCampanaRequest;
use App\Models\Campana;

class CampanaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Campana::all();
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
    public function store(StoreCampanaRequest $request)
    {
        $campana = Campana::create($request->validated());
        return response()->json(['message' => 'Campaña creada correctamente', 'campana' => $campana], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Campana $campana)
    {
        return response()->json(['campana' => $campana], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campana $campana)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCampanaRequest $request, Campana $campana)
    {
        $campana->update($request->validated());
        return response()->json(['message' => 'Campaña actualizada correctamente', 'campana' => $campana], 200);    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campana $campana)
    {
        $campana->delete();
        return response()->json(['message' => 'Campaña eliminada correctamente'], 200);
    }
}
