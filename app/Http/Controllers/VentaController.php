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
use Illuminate\Support\Facades\Log;
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
            // Líneas reales para Mis compras / Mis ventas (navegación a prendas).
            ->with(['detalle_ventas.articulo:id,nombre'])
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

    /**
     * Historial consolidado de adquisición del comprador autenticado.
     * GET /api/mis-articulos-adquiridos
     *
     * Regla: adquirido si existe al menos una venta entregada.
     * Cancelada nunca cuenta. Una cancelada posterior no borra adquisición previa.
     */
    public function articulosAdquiridos(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $userId = (int) $user->id;
        $estadosOk = VentaAutoCompleteService::ESTADOS_ADQUIRIDOS;

        $adquiridos = DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.user_id', $userId)
            ->where('detalle_ventas.articulo_id', '>', 0)
            ->where(function ($q) use ($estadosOk) {
                foreach ($estadosOk as $est) {
                    $q->orWhereRaw('LOWER(TRIM(ventas.estado)) = ?', [$est]);
                }
            })
            ->distinct()
            ->orderBy('detalle_ventas.articulo_id')
            ->pluck('detalle_ventas.articulo_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $enCurso = [
            'pendiente',
            'pendiente_activacion',
            'listo_pagar',
            'pago_acreditado',
            'en_curso',
        ];

        $enProceso = DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.user_id', $userId)
            ->where('detalle_ventas.articulo_id', '>', 0)
            ->where(function ($q) use ($enCurso) {
                foreach ($enCurso as $est) {
                    $q->orWhereRaw('LOWER(TRIM(ventas.estado)) = ?', [$est]);
                }
            })
            ->when(! empty($adquiridos), function ($q) use ($adquiridos) {
                $q->whereNotIn('detalle_ventas.articulo_id', $adquiridos);
            })
            ->distinct()
            ->orderBy('detalle_ventas.articulo_id')
            ->pluck('detalle_ventas.articulo_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'adquiridos' => array_values($adquiridos),
                'en_proceso' => array_values($enProceso),
            ],
            'meta' => [
                'count_adquiridos' => count($adquiridos),
                'count_en_proceso' => count($enProceso),
                'articulo_ids' => array_values($adquiridos),
                'regla' => 'adquirido_si_existe_entregado',
            ],
        ]);
    }

    /**
     * Estado de adquisición de UN artículo (historial completo del user).
     */
    public function articuloAdquisicion(Request $request, int $articuloId)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        if ($articuloId <= 0) {
            return response()->json([
                'adquirido' => false,
                'en_proceso' => false,
                'articulo_id' => $articuloId,
            ]);
        }

        $userId = (int) $user->id;
        $estadosOk = VentaAutoCompleteService::ESTADOS_ADQUIRIDOS;

        $adquirido = DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.user_id', $userId)
            ->where('detalle_ventas.articulo_id', $articuloId)
            ->where(function ($q) use ($estadosOk) {
                foreach ($estadosOk as $est) {
                    $q->orWhereRaw('LOWER(TRIM(ventas.estado)) = ?', [$est]);
                }
            })
            ->exists();

        $enProceso = false;
        if (! $adquirido) {
            $enCurso = [
                'pendiente',
                'pendiente_activacion',
                'listo_pagar',
                'pago_acreditado',
                'en_curso',
            ];
            $enProceso = DetalleVenta::query()
                ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
                ->where('ventas.user_id', $userId)
                ->where('detalle_ventas.articulo_id', $articuloId)
                ->where(function ($q) use ($enCurso) {
                    foreach ($enCurso as $est) {
                        $q->orWhereRaw('LOWER(TRIM(ventas.estado)) = ?', [$est]);
                    }
                })
                ->exists();
        }

        return response()->json([
            'articulo_id' => $articuloId,
            'adquirido' => (bool) $adquirido,
            'en_proceso' => (bool) $enProceso,
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
        try {
            $user = $request->user('api');
            if (! $user) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
                ], 401);
            }

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

            $metodo = strtolower(trim((string) ($data['metodo_pago'] ?? '')));
            if ($metodo !== '' && ! in_array($metodo, ['tarjeta', 'efectivo'], true)) {
                throw ValidationException::withMessages([
                    'metodo_pago' => ['Método de pago no válido.'],
                ]);
            }
            if ($metodo === '') {
                $metodo = null; // legacy / compat
            }

            $venta = DB::transaction(function () use ($user, $qtyByArticulo, $formaPagoId, $carritoReserva, $metodo) {
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

                // Estado inicial según método de pago simulado.
                if ($metodo === 'tarjeta') {
                    $estado = 'pago_acreditado';
                    $autoCompleteAt = null;
                    $nextStateAt = now()->addMinutes(VentaAutoCompleteService::MINUTOS_PASO);
                } elseif ($metodo === 'efectivo') {
                    $estado = 'pendiente_activacion';
                    $autoCompleteAt = null;
                    $nextStateAt = null;
                } else {
                    // Legacy: pendiente → entregado en 5 min.
                    $estado = 'pendiente';
                    $autoCompleteAt = now()->addMinutes(VentaAutoCompleteService::MINUTOS_AUTO_COMPLETE);
                    $nextStateAt = null;
                }

                $venta = Venta::create([
                    'user_id' => (int) $user->id,
                    'forma_pago_id' => $formaPagoId,
                    'tienda_id' => $tiendaId,
                    'total' => round($total, 2),
                    'estado' => $estado,
                    'metodo_pago' => $metodo,
                    'codigo_barras' => null,
                    'auto_complete_at' => $autoCompleteAt,
                    'next_state_at' => $nextStateAt,
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
                }

                return $venta;
            });

            $venta->load([
                'detalle_ventas.articulo:id,nombre',
                'user:id,nombre',
                'forma_pago:id,nombre',
            ]);

            try {
                $notificaciones->compraPendiente($venta);
            } catch (\Throwable $e) {
                Log::warning('Notificación de compra falló: '.$e->getMessage(), [
                    'venta_id' => $venta->id,
                ]);
            }

            $msg = match ($venta->metodo_pago) {
                'efectivo' => 'Tu solicitud fue enviada al vendedor',
                'tarjeta' => 'Tu pago fue acreditado',
                default => 'Compra registrada correctamente',
            };

            return response()->json([
                'message' => $msg,
                'venta' => $venta,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al registrar compra: '.$e->getMessage(), [
                'user_id' => $request->user('api')?->id,
                'exception' => $e::class,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'No se pudo registrar la compra. Intenta de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
        if (! in_array($estado, VentaAutoCompleteService::ESTADOS_CANCELABLES, true)) {
            return response()->json([
                'message' => 'Esta compra ya no se puede cancelar.',
                'estado' => $venta->estado,
            ], 422);
        }

        $venta = DB::transaction(function () use ($venta) {
            /** @var Venta $locked */
            $locked = Venta::query()->whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $estadoLocked = strtolower(trim((string) $locked->estado));
            if (! in_array($estadoLocked, VentaAutoCompleteService::ESTADOS_CANCELABLES, true)) {
                throw ValidationException::withMessages([
                    'estado' => ['Esta compra ya no se puede cancelar.'],
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
            $locked->next_state_at = null;
            $locked->auto_complete_at = null;
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

    /**
     * Vendedor activa pago en efectivo: genera código de barras y deja listo para pagar.
     * POST /api/ventas/{venta}/activar-efectivo
     */
    public function activarEfectivo(
        Request $request,
        Venta $venta,
        VentaAutoCompleteService $autoComplete,
        NotificacionService $notificaciones,
    ) {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        if (! $user->hasRole('admin')) {
            if (! $user->hasRole('vendedor')) {
                return response()->json(['message' => 'Solo el vendedor puede activar el pago.'], 403);
            }
            $user->loadMissing('vendedor');
            $tiendaId = $user->vendedor?->tienda_id;
            if (! $tiendaId || (int) $tiendaId !== (int) $venta->tienda_id) {
                return response()->json(['message' => 'No puedes activar esta compra.'], 403);
            }
        }

        try {
            $venta = $autoComplete->activarEfectivo($venta);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Error al activar efectivo: '.$e->getMessage());

            return response()->json([
                'message' => 'No se pudo activar el pago en efectivo. Intenta de nuevo.',
            ], 500);
        }

        $venta->load([
            'detalle_ventas.articulo:id,nombre',
            'user:id,nombre',
            'forma_pago:id,nombre',
        ]);

        try {
            $notificaciones->efectivoActivadoComprador($venta);
        } catch (\Throwable $e) {
            Log::warning('Notificación efectivo activado falló: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'El vendedor activó tu pago en efectivo',
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
