<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        return Resena::create($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResenaRequest $request)
    {
        return Resena::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Resena $resena)
    {
        return $resena;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resena $resena)
    {
        return $resena;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResenaRequest $request, Resena $resena)
    {
        $resena->update($request->validated());
        return $resena;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resena $resena)
    {
        $resena->delete();
        return response()->noContent();
    }
}
