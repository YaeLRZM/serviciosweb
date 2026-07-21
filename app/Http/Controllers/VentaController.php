<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Articulo;
use App\Models\DetalleVenta;
use App\Models\FormaPago;
use App\Models\Venta;
use App\Services\CarritoReservaService;
use App\Services\NotificacionService;
use App\Services\VentaAutoCompleteService;
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
    public function index(Request $request, VentaAutoCompleteService $autoComplete)
    {
        // Completar compras cuyo temporizador venció (fuente de verdad backend).
        $autoComplete->completarVencidas();

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
     * - stock validado y decrementado (reserva) en transacción
     * - estado inicial: pendiente (cancelable por el comprador)
     */
    public function store(
        StoreVentaRequest $request,
        NotificacionService $notificaciones,
        CarritoReservaService $carritoReserva,
    ) {
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

        $venta = DB::transaction(function () use ($user, $qtyByArticulo, $formaPagoId, $carritoReserva) {
            $articuloIds = array_keys($qtyByArticulo);

            // Consumir reservas de carrito (stock ya descontado al reservar).
            $carritoReserva->consumirReservasAlComprar($user, $qtyByArticulo);

            // Bloqueo pesimista para armar líneas y total.
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
                // Pago de prueba: pendiente 5 min; luego auto-completa el backend.
                'estado' => 'pendiente',
                'auto_complete_at' => now()->addMinutes(5),
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
                // Stock ya ajustado vía carrito/reserva (consumirReservasAlComprar).
            }

            return $venta;
        });

        $venta->load([
            'detalle_ventas.articulo:id,nombre',
            'user:id,nombre',
            'forma_pago:id,nombre',
        ]);

        $notificaciones->compraPendiente($venta);

        return response()->json([
            'message' => 'Compra registrada correctamente',
            'venta' => $venta,
        ], 201);
    }

    /**
     * Detalle de una venta (solo si pertenece al scope del usuario).
     */
    public function show(Request $request, Venta $venta, VentaAutoCompleteService $autoComplete)
    {
        $autoComplete->completarVencidas();
        $venta->refresh();

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

    /**
     * Cancela una compra del comprador dueño.
     * - Solo user_id dueño (o admin)
     * - Solo estado pendiente
     * - Restaura stock y re-publica artículo si stock > 0
     */
    public function cancelar(Request $request, Venta $venta)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $esDueño = (int) $user->id === (int) $venta->user_id;
        $esAdmin = $user->hasRole('admin');
        if (! $esDueño && ! $esAdmin) {
            return response()->json([
                'message' => 'No puedes cancelar esta compra.',
            ], 403);
        }

        $estado = strtolower(trim((string) $venta->estado));
        if ($estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo las compras pendientes se pueden cancelar.',
                'estado' => $venta->estado,
            ], 422);
        }

        $venta = DB::transaction(function () use ($venta) {
            /** @var Venta $locked */
            $locked = Venta::query()->whereKey($venta->id)->lockForUpdate()->firstOrFail();
            if (strtolower(trim((string) $locked->estado)) !== 'pendiente') {
                throw ValidationException::withMessages([
                    'estado' => ['Solo las compras pendientes se pueden cancelar.'],
                ]);
            }

            $lineas = DetalleVenta::query()
                ->where('venta_id', $locked->id)
                ->lockForUpdate()
                ->get();

            foreach ($lineas as $linea) {
                $articulo = Articulo::query()
                    ->whereKey((int) $linea->articulo_id)
                    ->lockForUpdate()
                    ->first();
                if (! $articulo) {
                    continue;
                }
                $articulo->stock = (int) $articulo->stock + (int) $linea->cantidad;
                if ((int) $articulo->stock > 0) {
                    $articulo->disponible = true;
                }
                $articulo->save();
            }

            $locked->estado = 'cancelada';
            $locked->save();

            return $locked;
        });

        $venta->load([
            'detalle_ventas.articulo:id,nombre',
            'user:id,nombre',
            'forma_pago:id,nombre',
        ]);

        return response()->json([
            'message' => 'Compra cancelada correctamente',
            'venta' => $venta,
        ], 200);
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
