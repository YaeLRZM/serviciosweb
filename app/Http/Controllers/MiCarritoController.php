<?php

namespace App\Http\Controllers;

use App\Services\CarritoReservaService;
use Illuminate\Http\Request;

class MiCarritoController extends Controller
{
    public function show(Request $request, CarritoReservaService $carrito)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $snap = $carrito->snapshot($user);

        return response()->json([
            'data' => $snap['items'],
            'meta' => [
                'liberados' => $snap['liberados'],
                'reserva_minutos' => CarritoReservaService::RESERVA_MINUTOS,
            ],
        ]);
    }

    public function agregar(Request $request, CarritoReservaService $carrito)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $snap = $carrito->agregar($user, (int) $data['articulo_id'], (int) $data['cantidad']);

        return response()->json([
            'message' => 'Artículo reservado en tu carrito',
            'data' => $snap['items'],
            'meta' => [
                'liberados' => $snap['liberados'],
                'reserva_minutos' => CarritoReservaService::RESERVA_MINUTOS,
            ],
        ], 201);
    }

    public function actualizar(Request $request, int $articuloId, CarritoReservaService $carrito)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $snap = $carrito->actualizarCantidad($user, $articuloId, (int) $data['cantidad']);

        return response()->json([
            'message' => 'Carrito actualizado',
            'data' => $snap['items'],
            'meta' => [
                'liberados' => $snap['liberados'],
                'reserva_minutos' => CarritoReservaService::RESERVA_MINUTOS,
            ],
        ]);
    }

    public function quitar(Request $request, int $articuloId, CarritoReservaService $carrito)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $snap = $carrito->quitar($user, $articuloId);

        return response()->json([
            'message' => 'Artículo eliminado del carrito',
            'data' => $snap['items'],
            'meta' => [
                'liberados' => $snap['liberados'],
                'reserva_minutos' => CarritoReservaService::RESERVA_MINUTOS,
            ],
        ]);
    }

    public function vaciar(Request $request, CarritoReservaService $carrito)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $snap = $carrito->vaciar($user);

        return response()->json([
            'message' => 'Carrito vaciado',
            'data' => $snap['items'],
            'meta' => [
                'liberados' => $snap['liberados'],
                'reserva_minutos' => CarritoReservaService::RESERVA_MINUTOS,
            ],
        ]);
    }
}
