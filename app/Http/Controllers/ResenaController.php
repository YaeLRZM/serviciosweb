<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyResenaRequest;
use App\Http\Requests\IndexResenaRequest;
use App\Http\Requests\ShowResenaRequest;
use App\Http\Requests\StoreResenaRequest;
use App\Http\Requests\UpdateResenaRequest;
use App\Models\Resena;

class ResenaController extends Controller
{
    public function index(IndexResenaRequest $request)
    {
        return Resena::all();
    }

    public function store(StoreResenaRequest $request)
    {
        return Resena::create($request->validated());
    }

    public function show(ShowResenaRequest $request, Resena $resena)
    {
        return $resena;
    }

    public function update(UpdateResenaRequest $request, Resena $resena)
    {
        $resena->update($request->validated());

        return $resena;
    }

    public function destroy(DestroyResenaRequest $request, Resena $resena)
    {
        $resena->delete();

        return response()->noContent();
    }
}
