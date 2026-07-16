<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResenaRequest;
use App\Http\Requests\UpdateResenaRequest;
use App\Models\Resena;

class ResenaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Resena::all();
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
    public function store(StoreResenaRequest $request)
    {
        $resena = Resena::create($request->validated());
        return response()->json(['message' => 'Reseña creada correctamente', 'resena' => $resena], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resena $resena)
    {
        return response()->json(['resena' => $resena], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resena $resena)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResenaRequest $request, Resena $resena)
    {
        $resena->update($request->validated());
        return response()->json(['message' => 'Reseña actualizada correctamente', 'resena' => $resena], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resena $resena)
    {
        $resena->delete();
        return response()->json(['message' => 'Reseña eliminada correctamente'], 200);
    }
}
