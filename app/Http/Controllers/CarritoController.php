<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarritoRequest;
use App\Http\Requests\UpdateCarritoRequest;
use App\Models\Carrito;
use Illuminate\Http\Request;

/**
 * API de carrito server-side: no usada por la app (carrito local).
 * Endpoints de lectura con ownership; escritura sigue cerrada (FormRequest authorize false).
 */
class CarritoController extends Controller
{
    /**
     * Solo carritos del usuario autenticado (admin: todos).
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('admin')) {
            return Carrito::query()->orderByDesc('id')->get();
        }

        return Carrito::query()
            ->where('user_id', (int) $user->id)
            ->orderByDesc('id')
            ->get();
    }

    public function create()
    {
        //
    }

    public function store(StoreCarritoRequest $request)
    {
        // StoreCarritoRequest::authorize() === false (no abrir escritura aún).
        $carrito = Carrito::create($request->validated());

        return response()->json(['message' => 'Carrito creado correctamente', 'carrito' => $carrito], 201);
    }

    public function show(Request $request, Carrito $carrito)
    {
        if (! $this->userCanAccessCarrito($request->user('api'), $carrito)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return response()->json(['carrito' => $carrito], 200);
    }

    public function edit(Carrito $carrito)
    {
        //
    }

    public function update(UpdateCarritoRequest $request, Carrito $carrito)
    {
        // UpdateCarritoRequest::authorize() === false.
        $carrito->update($request->validated());

        return response()->json(['message' => 'Carrito actualizado correctamente', 'carrito' => $carrito], 200);
    }

    public function destroy(Request $request, Carrito $carrito)
    {
        if (! $this->userCanAccessCarrito($request->user('api'), $carrito)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $carrito->delete();

        return response()->json(['message' => 'Carrito eliminado correctamente'], 200);
    }

    private function userCanAccessCarrito($user, Carrito $carrito): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $user->id === (int) $carrito->user_id;
    }
}
