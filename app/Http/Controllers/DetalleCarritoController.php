<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalleCarritoRequest;
use App\Http\Requests\UpdateDetalleCarritoRequest;
use App\Models\DetalleCarrito;
use Illuminate\Http\Request;

/**
 * Detalle de carrito server-side: no usado por la app (carrito local).
 * Lectura con ownership; escritura sigue cerrada (FormRequest authorize false).
 */
class DetalleCarritoController extends Controller
{
    /**
     * Solo líneas de carritos del usuario (admin: todas).
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('admin')) {
            return DetalleCarrito::query()->orderByDesc('id')->get();
        }

        return DetalleCarrito::query()
            ->whereHas('carrito', fn ($q) => $q->where('user_id', (int) $user->id))
            ->orderByDesc('id')
            ->get();
    }

    public function create()
    {
        //
    }

    public function store(StoreDetalleCarritoRequest $request)
    {
        // StoreDetalleCarritoRequest::authorize() === false.
        return DetalleCarrito::create($request->all());
    }

    public function show(Request $request, DetalleCarrito $detalleCarrito)
    {
        if (! $this->userCanAccessDetalle($request->user('api'), $detalleCarrito)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return response()->json(['detalleCarrito' => $detalleCarrito], 200);
    }

    public function edit(DetalleCarrito $detalleCarrito)
    {
        //
    }

    public function update(UpdateDetalleCarritoRequest $request, DetalleCarrito $detalleCarrito)
    {
        // UpdateDetalleCarritoRequest::authorize() === false.
        return $detalleCarrito->update($request->all());
    }

    public function destroy(Request $request, DetalleCarrito $detalleCarrito)
    {
        if (! $this->userCanAccessDetalle($request->user('api'), $detalleCarrito)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $detalleCarrito->delete();

        return response()->json(['message' => 'Detalle de carrito eliminado'], 200);
    }

    private function userCanAccessDetalle($user, DetalleCarrito $detalle): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        $detalle->loadMissing('carrito');

        return $detalle->carrito
            && (int) $detalle->carrito->user_id === (int) $user->id;
    }
}
