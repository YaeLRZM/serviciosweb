<?php

namespace App\Services\Admin;

use App\Models\Articulo;
use App\Models\DetalleVenta;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaAutoCompleteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Acciones delicadas de ventas SOLO para el panel administrador web.
 *
 * - Cancelar: solo etapas previas a entrega (sin dinero finalizado / en tránsito de entrega).
 * - Devolver dinero: solo compras ya pagadas/entregadas (o en curso post-pago);
 *   pasa por devolucion_en_proceso (2 min) → devuelto.
 *
 * No se expone por API móvil a vendedor/cliente.
 */
class AdminVentaAccionesService
{
    /** Mismos estados cancelables que el comprador (antes de entregado). */
    public const ESTADOS_CANCELABLES = VentaAutoCompleteService::ESTADOS_CANCELABLES;

    /**
     * Solo se puede iniciar devolución si el dinero ya entró en el flujo de pago.
     * No se devuelve una compra que aún no pagó (usar cancelar).
     */
    public const ESTADOS_DEVOLUCION = [
        'pago_acreditado',
        'en_curso',
        'entregado',
        'completada', // legacy
    ];

    public const ESTADO_DEVOLUCION_EN_PROCESO = 'devolucion_en_proceso';
    public const ESTADO_DEVUELTO = 'devuelto';
    public const ESTADO_CANCELADA = 'cancelada';

    public function __construct(
        private readonly VentaAutoCompleteService $autoComplete,
    ) {}

    public function assertAdmin(User $user): void
    {
        if (! $user->hasRole('admin')) {
            throw new InvalidArgumentException('Solo un administrador puede realizar esta acción.');
        }
    }

    public function sePuedeCancelar(Venta|string $ventaOEstado): bool
    {
        $estado = $ventaOEstado instanceof Venta
            ? strtolower(trim((string) $ventaOEstado->estado))
            : strtolower(trim((string) $ventaOEstado));

        return in_array($estado, self::ESTADOS_CANCELABLES, true);
    }

    public function sePuedeIniciarDevolucion(Venta|string $ventaOEstado): bool
    {
        $estado = $ventaOEstado instanceof Venta
            ? strtolower(trim((string) $ventaOEstado->estado))
            : strtolower(trim((string) $ventaOEstado));

        return in_array($estado, self::ESTADOS_DEVOLUCION, true);
    }

    /**
     * Cancela la venta desde administración y restaura stock.
     *
     * @throws InvalidArgumentException
     */
    public function cancelar(User $admin, int $ventaId): Venta
    {
        $this->assertAdmin($admin);

        return DB::transaction(function () use ($admin, $ventaId) {
            /** @var Venta $locked */
            $locked = Venta::query()->whereKey($ventaId)->lockForUpdate()->firstOrFail();
            $estado = strtolower(trim((string) $locked->estado));

            if ($estado === self::ESTADO_CANCELADA) {
                throw new InvalidArgumentException('Esta venta ya está cancelada.');
            }
            if (in_array($estado, [self::ESTADO_DEVOLUCION_EN_PROCESO, self::ESTADO_DEVUELTO], true)) {
                throw new InvalidArgumentException('Esta venta está en devolución o ya fue devuelta. No se puede cancelar.');
            }
            if (! $this->sePuedeCancelar($estado)) {
                throw new InvalidArgumentException(
                    'Esta venta ya no puede cancelarse. Si el pago ya se completó, usa la devolución de dinero.'
                );
            }

            $this->restaurarStock($locked);

            $locked->estado = self::ESTADO_CANCELADA;
            $locked->next_state_at = null;
            $locked->auto_complete_at = null;
            $locked->admin_nota = 'Cancelada por administración';
            $locked->admin_user_id = (int) $admin->id;
            $locked->admin_accion_at = now();
            $locked->save();

            Log::info('Admin canceló venta', [
                'venta_id' => $locked->id,
                'admin_id' => $admin->id,
                'estado_previo' => $estado,
            ]);

            return $locked->fresh([
                'user',
                'tienda',
                'detalle_ventas.articulo',
            ]);
        });
    }

    /**
     * Inicia devolución: devolucion_en_proceso + next_state_at en 2 minutos.
     * El paso a “devuelto” lo hace VentaAutoCompleteService.
     *
     * @throws InvalidArgumentException
     */
    public function iniciarDevolucion(User $admin, int $ventaId): Venta
    {
        $this->assertAdmin($admin);

        return DB::transaction(function () use ($admin, $ventaId) {
            /** @var Venta $locked */
            $locked = Venta::query()->whereKey($ventaId)->lockForUpdate()->firstOrFail();
            $estado = strtolower(trim((string) $locked->estado));

            if ($estado === self::ESTADO_DEVUELTO) {
                throw new InvalidArgumentException('Esta venta ya fue devuelta.');
            }
            if ($estado === self::ESTADO_DEVOLUCION_EN_PROCESO) {
                throw new InvalidArgumentException('La devolución ya está en proceso. Espera a que termine.');
            }
            if ($estado === self::ESTADO_CANCELADA) {
                throw new InvalidArgumentException('No se puede devolver una venta cancelada.');
            }
            if (! $this->sePuedeIniciarDevolucion($estado)) {
                throw new InvalidArgumentException(
                    'Esta venta aún no admite devolución. Si no se ha cobrado, cancélala en su lugar.'
                );
            }

            $locked->estado = self::ESTADO_DEVOLUCION_EN_PROCESO;
            $locked->next_state_at = now()->addMinutes(VentaAutoCompleteService::MINUTOS_PASO);
            $locked->auto_complete_at = null;
            $locked->admin_nota = 'Devolución de dinero iniciada por administración';
            $locked->admin_user_id = (int) $admin->id;
            $locked->admin_accion_at = now();
            $locked->save();

            Log::info('Admin inició devolución de venta', [
                'venta_id' => $locked->id,
                'admin_id' => $admin->id,
                'estado_previo' => $estado,
                'next_state_at' => optional($locked->next_state_at)?->toIso8601String(),
            ]);

            return $locked->fresh([
                'user',
                'tienda',
                'detalle_ventas.articulo',
            ]);
        });
    }

    /**
     * Completa devoluciones vencidas: devolucion_en_proceso → devuelto (+ restaura stock).
     *
     * @return int cantidad actualizada
     */
    public function completarDevolucionesVencidas(): int
    {
        $ids = Venta::query()
            ->where('estado', self::ESTADO_DEVOLUCION_EN_PROCESO)
            ->whereNotNull('next_state_at')
            ->where('next_state_at', '<=', now())
            ->orderBy('id')
            ->limit(100)
            ->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            try {
                $ok = DB::transaction(function () use ($id) {
                    /** @var Venta|null $locked */
                    $locked = Venta::query()->whereKey($id)->lockForUpdate()->first();
                    if (! $locked) {
                        return false;
                    }
                    if (strtolower(trim((string) $locked->estado)) !== self::ESTADO_DEVOLUCION_EN_PROCESO) {
                        return false;
                    }
                    if (! $locked->next_state_at || $locked->next_state_at->isFuture()) {
                        return false;
                    }

                    $this->restaurarStock($locked);

                    $locked->estado = self::ESTADO_DEVUELTO;
                    $locked->next_state_at = null;
                    $nota = trim((string) ($locked->admin_nota ?? ''));
                    $locked->admin_nota = ($nota !== '' ? $nota.' · ' : '')
                        .'Devolución completada automáticamente';
                    $locked->admin_accion_at = now();
                    $locked->save();

                    return true;
                });
                if ($ok) {
                    $count++;
                    Log::info('Devolución de venta completada automáticamente', ['venta_id' => $id]);
                }
            } catch (\Throwable $e) {
                Log::warning("No se pudo completar devolución de venta {$id}: ".$e->getMessage());
            }
        }

        return $count;
    }

    protected function restaurarStock(Venta $venta): void
    {
        $lineas = DetalleVenta::query()
            ->where('venta_id', $venta->id)
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
    }
}
