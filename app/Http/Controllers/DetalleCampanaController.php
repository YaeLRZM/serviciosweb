<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalleCampanaRequest;
use App\Http\Requests\UpdateDetalleCampanaRequest;
use App\Models\DetalleCampana;

class DetalleCampanaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetalleCampana::all();
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
    public function store(StoreDetalleCampanaRequest $request)
    {
        $detalleCampana = DetalleCampana::create($request->validated());
        return response()->json(['message' => 'Detalle de campaña creado correctamente', 'detalleCampana' => $detalleCampana], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DetalleCampana $detalleCampana)
    {
        return response()->json(['detalleCampana' => $detalleCampana], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DetalleCampana $detalleCampana)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetalleCampanaRequest $request, DetalleCampana $detalleCampana)
    {
        $detalleCampana->update($request->validated());
        return response()->json(['message' => 'Detalle de campaña actualizado correctamente', 'detalleCampana' => $detalleCampana], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleCampana $detalleCampana)
    {
        $detalleCampana->delete();
        return response()->json(['message' => 'Detalle de campaña eliminado correctamente'], 200);
    }
}
