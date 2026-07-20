<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    /**
     * Listado de ventas con scope por rol (ownership).
     * - vendedor: solo ventas de su tienda
     * - user: solo sus compras (user_id)
     * - admin: todas (opcional ?tienda=)
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $query = Venta::query()
            ->withCount('detalle_ventas')
            ->orderByDesc('id');

        if ($user->hasRole('admin')) {
            if ($request->filled('tienda')) {
                $query->where('tienda_id', (int) $request->query('tienda'));
            }
        } elseif ($user->hasRole('vendedor')) {
            $user->loadMissing('vendedor');
            $tiendaId = $user->vendedor?->tienda_id;
            if (! $tiendaId) {
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'count' => 0,
                        'suma_totales' => 0,
                    ],
                ]);
            }
            $query->where('tienda_id', (int) $tiendaId);
        } elseif ($user->hasRole('user')) {
            $query->where('user_id', (int) $user->id);
        } else {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $ventas = $query->get();
        $suma = (float) $ventas->sum('total');

        return response()->json([
            'data' => $ventas,
            'meta' => [
                'count' => $ventas->count(),
                'suma_totales' => round($suma, 2),
            ],
        ]);
    }

    public function create()
    {
        //
    }

    public function store(StoreVentaRequest $request)
    {
        return Venta::create($request->all());
    }

    /**
     * Detalle de una venta (solo si pertenece al scope del usuario).
     */
    public function show(Request $request, Venta $venta)
    {
        $user = $request->user('api');
        if (! $user || ! $this->userCanViewVenta($user, $venta)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        // Enriquecimiento mínimo y seguro (solo ids + nombres reales).
        // Ownership ya validado arriba; no se expone más de lo necesario.
        $venta->load([
            'detalle_ventas.articulo:id,nombre',
            'user:id,nombre',
            'forma_pago:id,nombre',
        ]);

        return response()->json(['venta' => $venta], 200);
    }

    public function edit(Venta $venta)
    {
        //
    }

    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        return $venta->update($request->all());
    }

    public function destroy(Venta $venta)
    {
        return $venta->delete();
    }

    private function userCanViewVenta($user, Venta $venta): bool
    {
        if ($user->hasRole('admin')) {
            return true;
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
