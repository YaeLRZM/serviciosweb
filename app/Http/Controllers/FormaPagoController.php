<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForma_PagoRequest;
use App\Http\Requests\UpdateForma_PagoRequest;
use App\Models\FormaPago;

class FormaPagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FormaPago::all();
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
    public function store(StoreForma_PagoRequest $request)
    {
        FormaPago::create($request->validated());
        return response()->json(['message' => 'Forma de pago creada correctamente', 'forma_pago' => $request->validated()]);
    }

    /**
     * Display the specified resource.
     */
    public function show(FormaPago $formaPago)
    {
        return $formaPago;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormaPago $formaPago)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateForma_PagoRequest $request, FormaPago $formaPago)
    {
        $formaPago->update($request->validated());
        return response()->json(['message' => 'Forma de pago actualizada correctamente', 'forma_pago' => $formaPago]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormaPago $formaPago)
    {
        $formaPago->delete();
        return response()->json(['message' => 'Forma de pago eliminada correctamente']);
    }
}
