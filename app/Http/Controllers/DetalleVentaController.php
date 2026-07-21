<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetalleVentaRequest;
use App\Http\Requests\UpdateDetalleVentaRequest;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;

/**
 * Detalles de venta sueltos: la creación real va por POST /ventas (transacción).
 * index/show con ownership; store/update siguen cerrados (authorize false).
 */
class DetalleVentaController extends Controller
{
    /**
     * No listado global: admin ve todo; user solo líneas de sus ventas;
     * vendedor solo líneas de ventas de su tienda.
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = DetalleVenta::query()->orderByDesc('id');

        if ($user->hasRole('admin')) {
            return $query->get();
        }

        if ($user->hasRole('vendedor')) {
            $user->loadMissing('vendedor');
            $tiendaId = $user->vendedor?->tienda_id;
            if (! $tiendaId) {
                return response()->json([]);
            }

            return $query
                ->whereHas('venta', fn ($q) => $q->where('tienda_id', (int) $tiendaId))
                ->get();
        }

        if ($user->hasRole('user')) {
            return $query
                ->whereHas('venta', fn ($q) => $q->where('user_id', (int) $user->id))
                ->get();
        }

        return response()->json(['message' => 'This action is unauthorized.'], 403);
    }

    public function store(StoreDetalleVentaRequest $request)
    {
        // StoreDetalleVentaRequest::authorize() === false — no crear líneas sueltas.
        $venta = DetalleVenta::create($request->validated());

        return response()->json([
            'message' => 'Detalle de venta creado correctamente',
            'detalleVenta' => $venta,
        ], 201);
    }

    public function show(Request $request, DetalleVenta $detalle_venta)
    {
        if (! $this->userCanAccessDetalle($request->user('api'), $detalle_venta)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return response()->json(['detalleVenta' => $detalle_venta], 200);
    }

    public function update(UpdateDetalleVentaRequest $request, DetalleVenta $detalle_venta)
    {
        // UpdateDetalleVentaRequest::authorize() === false.
        $detalle_venta->update($request->validated());

        return response()->json([
            'message' => 'Detalle de venta actualizado correctamente',
            'detalleVenta' => $detalle_venta,
        ], 200);
    }

    public function destroy(DetalleVenta $detalle_venta)
    {
        return response()->json(['message' => 'Eliminación de detalle de venta no permitida'], 403);
    }

    private function userCanAccessDetalle($user, DetalleVenta $detalle): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        $detalle->loadMissing('venta');
        $venta = $detalle->venta;
        if (! $venta) {
            return false;
        }

        if ($user->hasRole('vendedor')) {
            $user->loadMissing('vendedor');
            $tiendaId = $user->vendedor?->tienda_id;

            return $tiendaId && (int) $tiendaId === (int) $venta->tienda_id;
        }

        if ($user->hasRole('user')) {
            return (int) $user->id === (int) $venta->user_id;
        }

        return false;
    }
}
