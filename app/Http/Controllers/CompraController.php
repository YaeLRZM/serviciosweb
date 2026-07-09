<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyCompraRequest;
use App\Http\Requests\IndexCompraRequest;
use App\Http\Requests\ShowCompraRequest;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Models\Compra;

class CompraController extends Controller
{
    public function index(IndexCompraRequest $request)
    {
        return Compra::all();
    }

    public function store(StoreCompraRequest $request)
    {
        return Compra::create($request->validated());
    }

    public function show(ShowCompraRequest $request, Compra $compra)
    {
        return $compra;
    }

    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        $compra->update($request->validated());

        return $compra;
    }

    public function destroy(DestroyCompraRequest $request, Compra $compra)
    {
        $compra->delete();

        return response()->noContent();
    }
}
