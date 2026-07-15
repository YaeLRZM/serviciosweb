<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendedorRequest;
use App\Http\Requests\UpdateVendedorRequest;
use App\Http\Resources\VendedorResource;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vendedores = Vendedor::with(['tienda', 'user'])
            ->when($request->filled('tienda'), fn ($q) => $q->where('tienda_id', $request->input('tienda')))
            ->when($request->filled('estatus'), fn ($q) => $q->where('estatus', $request->input('estatus')))
            ->get();

        return VendedorResource::collection($vendedores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendedorRequest $request)
    {
        $vendedor = Vendedor::create($request->validated());

        return new VendedorResource($vendedor->load(['tienda', 'user']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendedor $vendedor)
    {
        $vendedor->load(['tienda', 'user']);

        return new VendedorResource($vendedor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendedorRequest $request, Vendedor $vendedor)
    {
        $vendedor->update($request->validated());

        return new VendedorResource($vendedor->load(['tienda', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendedor $vendedor)
    {
        $vendedor->delete();

        return response()->json(['message' => 'Vendedor eliminado correctamente.'], 200);
    }
}