<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Articulo;
use App\Models\DetalleVenta;
use App\Models\FormaPago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Compra mínima del usuario autenticado.
     * Body: { items: [{ articulo_id, cantidad }, ...], forma_pago_id? }
     * - user_id del token
     * - tienda_id derivada de los artículos (una sola tienda por compra)
     * - total calculado en servidor
     * - stock validado y decrementado en transacción
     * - estado: completada (sin pasarela de pago)
     */
    public function store(StoreVentaRequest $request)
    {
        $user = $request->user('api');
        $data = $request->validated();

        // Agrupar cantidades por articulo_id (por si el cliente manda duplicados).
        $qtyByArticulo = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['articulo_id'];
            $qtyByArticulo[$id] = ($qtyByArticulo[$id] ?? 0) + (int) $item['cantidad'];
        }

        $formaPagoId = isset($data['forma_pago_id'])
            ? (int) $data['forma_pago_id']
            : (int) (FormaPago::query()->orderBy('id')->value('id') ?? 0);

        if ($formaPagoId <= 0) {
            throw ValidationException::withMessages([
                'forma_pago_id' => ['No hay forma de pago configurada en el sistema.'],
            ]);
        }

        $venta = DB::transaction(function () use ($user, $qtyByArticulo, $formaPagoId) {
            $articuloIds = array_keys($qtyByArticulo);

            // Bloqueo pesimista para evitar oversell concurrente.
            $articulos = Articulo::query()
                ->whereIn('id', $articuloIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($articulos->count() !== count($articuloIds)) {
                throw ValidationException::withMessages([
                    'items' => ['Uno o más artículos no existen.'],
                ]);
            }

            $tiendaIds = $articulos->pluck('tienda_id')->unique()->values();
            if ($tiendaIds->count() !== 1) {
                throw ValidationException::withMessages([
                    'items' => [
                        'En esta versión solo puedes comprar artículos de una misma tienda. '
                        .'Quita del carrito los productos de otras tiendas e intenta de nuevo.',
                    ],
                ]);
            }
            $tiendaId = (int) $tiendaIds->first();

            $lineas = [];
            $total = 0.0;

            foreach ($qtyByArticulo as $articuloId => $cantidad) {
                /** @var Articulo $articulo */
                $articulo = $articulos->get($articuloId);

                if (! $articulo->disponible) {
                    throw ValidationException::withMessages([
                        'items' => ["El artículo #{$articuloId} no está disponible."],
                    ]);
                }

                if ((int) $articulo->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stock insuficiente para «{$articulo->nombre}» "
                            ."(disponible: {$articulo->stock}, solicitado: {$cantidad}).",
                        ],
                    ]);
                }

                $precio = (float) $articulo->precio;
                $subtotal = round($precio * $cantidad, 2);
                $total += $subtotal;

                $lineas[] = [
                    'articulo' => $articulo,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ];
            }

            $venta = Venta::create([
                'user_id' => (int) $user->id,
                'forma_pago_id' => $formaPagoId,
                'tienda_id' => $tiendaId,
                'total' => round($total, 2),
                // Sin pasarela: la compra se registra como completada.
                'estado' => 'completada',
            ]);

            foreach ($lineas as $linea) {
                /** @var Articulo $articulo */
                $articulo = $linea['articulo'];

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'articulo_id' => $articulo->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);

                $nuevoStock = (int) $articulo->stock - (int) $linea['cantidad'];
                $articulo->stock = $nuevoStock;
                // Si se agota, dejar de mostrarlo en catálogo público.
                if ($nuevoStock <= 0) {
                    $articulo->disponible = false;
                }
                $articulo->save();
            }

            return $venta;
        });

        $venta->load([
            'detalle_ventas.articulo:id,nombre',
            'user:id,nombre',
            'forma_pago:id,nombre',
        ]);

        return response()->json([
            'message' => 'Compra registrada correctamente',
            'venta' => $venta,
        ], 201);
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
