<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Avanza estados del flujo de pago simulado cuando vence next_state_at / auto_complete_at.
 * Estado final único: entregado (ya no existe “completada”).
 */
class VentaAutoCompleteService
{
    /** Legacy: minutos pendiente → entregado. */
    public const MINUTOS_AUTO_COMPLETE = 5;

    /** Nuevo flujo: minutos entre pasos de estado. */
    public const MINUTOS_PASO = 2;

    public const ESTADOS_CANCELABLES = [
        'pendiente',
        'pendiente_activacion',
        'listo_pagar',
        'pago_acreditado',
        'en_curso',
    ];

    /** Estado final / prenda adquirida. */
    public const ESTADOS_ADQUIRIDOS = [
        'entregado',
    ];

    public function __construct(
        private readonly NotificacionService $notificaciones,
    ) {}

    /**
     * Ejecuta todos los avances vencidos (nuevo flujo + legacy).
     *
     * @return int número de ventas actualizadas
     */
    public function completarVencidas(): int
    {
        $this->asegurarAutoCompleteAtPendientes();

        $n = 0;
        $n += $this->avanzarFlujoNuevo();
        $n += $this->completarLegacyPendientes();
        // Devoluciones admin: devolucion_en_proceso → devuelto (2 min).
        $n += app(\App\Services\Admin\AdminVentaAccionesService::class)
            ->completarDevolucionesVencidas();

        return $n;
    }

    /**
     * Secuencia con next_state_at:
     * listo_pagar → pago_acreditado → en_curso → entregado
     * (también pago_acreditado / en_curso que ya tengan next_state_at)
     */
    public function avanzarFlujoNuevo(): int
    {
        $transiciones = [
            'listo_pagar' => 'pago_acreditado',
            'pago_acreditado' => 'en_curso',
            'en_curso' => 'entregado',
        ];

        $count = 0;
        foreach ($transiciones as $desde => $hacia) {
            $ids = Venta::query()
                ->where('estado', $desde)
                ->whereNotNull('next_state_at')
                ->where('next_state_at', '<=', now())
                ->orderBy('id')
                ->limit(100)
                ->pluck('id');

            foreach ($ids as $id) {
                try {
                    $updated = DB::transaction(function () use ($id, $desde, $hacia) {
                        /** @var Venta|null $venta */
                        $venta = Venta::query()->whereKey($id)->lockForUpdate()->first();
                        if (! $venta) {
                            return null;
                        }
                        if (strtolower(trim((string) $venta->estado)) !== $desde) {
                            return null;
                        }
                        if (! $venta->next_state_at || $venta->next_state_at->isFuture()) {
                            return null;
                        }

                        $venta->estado = $hacia;
                        if ($hacia === 'entregado') {
                            $venta->next_state_at = null;
                        } else {
                            $venta->next_state_at = now()->addMinutes(self::MINUTOS_PASO);
                        }
                        $venta->save();

                        return $venta;
                    });

                    if ($updated) {
                        $this->notificarCambioEstado($updated);
                        $count++;
                    }
                } catch (\Throwable $e) {
                    Log::warning("No se pudo avanzar venta {$id} de {$desde}: ".$e->getMessage());
                }
            }
        }

        return $count;
    }

    /**
     * Legacy: pendiente + auto_complete_at → entregado (estado final único).
     */
    public function completarLegacyPendientes(): int
    {
        $ids = Venta::query()
            ->where('estado', 'pendiente')
            ->whereNotNull('auto_complete_at')
            ->where('auto_complete_at', '<=', now())
            ->orderBy('id')
            ->limit(100)
            ->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            try {
                $updated = DB::transaction(function () use ($id) {
                    /** @var Venta|null $venta */
                    $venta = Venta::query()->whereKey($id)->lockForUpdate()->first();
                    if (! $venta) {
                        return null;
                    }
                    if (strtolower(trim((string) $venta->estado)) !== 'pendiente') {
                        return null;
                    }
                    if (! $venta->auto_complete_at || $venta->auto_complete_at->isFuture()) {
                        return null;
                    }

                    $venta->estado = 'entregado';
                    $venta->next_state_at = null;
                    $venta->save();

                    return $venta;
                });

                if ($updated) {
                    $this->notificaciones->pedidoEntregado($updated);
                    $count++;
                }
            } catch (\Throwable $e) {
                Log::warning("No se pudo auto-entregar venta legacy {$id}: ".$e->getMessage());
            }
        }

        return $count;
    }

    public function asegurarAutoCompleteAtPendientes(): int
    {
        $ids = Venta::query()
            ->where('estado', 'pendiente')
            ->whereNull('auto_complete_at')
            ->orderBy('id')
            ->limit(200)
            ->pluck('id');

        $fixed = 0;
        foreach ($ids as $id) {
            try {
                $ok = DB::transaction(function () use ($id) {
                    /** @var Venta|null $venta */
                    $venta = Venta::query()->whereKey($id)->lockForUpdate()->first();
                    if (! $venta) {
                        return false;
                    }
                    if (strtolower(trim((string) $venta->estado)) !== 'pendiente') {
                        return false;
                    }
                    if ($venta->auto_complete_at) {
                        return false;
                    }

                    $base = $venta->created_at ?? now();
                    $venta->auto_complete_at = $base->copy()->addMinutes(self::MINUTOS_AUTO_COMPLETE);
                    $venta->save();

                    return true;
                });
                if ($ok) {
                    $fixed++;
                }
            } catch (\Throwable $e) {
                Log::warning("No se pudo asignar auto_complete_at a venta {$id}: ".$e->getMessage());
            }
        }

        return $fixed;
    }

    /**
     * Activa pago en efectivo: genera código de barras y deja listo para pagar.
     */
    public function activarEfectivo(Venta $venta): Venta
    {
        return DB::transaction(function () use ($venta) {
            /** @var Venta $locked */
            $locked = Venta::query()->whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $estado = strtolower(trim((string) $locked->estado));
            if ($estado !== 'pendiente_activacion') {
                throw new \InvalidArgumentException(
                    'Solo las compras en pendiente de activación se pueden activar.'
                );
            }
            if (strtolower(trim((string) ($locked->metodo_pago ?? ''))) !== 'efectivo') {
                throw new \InvalidArgumentException('Esta compra no es de pago en efectivo.');
            }

            $locked->codigo_barras = $this->generarCodigoBarras($locked->id);
            $locked->estado = 'listo_pagar';
            $locked->next_state_at = now()->addMinutes(self::MINUTOS_PASO);
            $locked->save();

            return $locked;
        });
    }

    public function generarCodigoBarras(int $ventaId): string
    {
        // Código simulado único y estable por venta (no es pasarela real).
        $raw = 'IXE'.str_pad((string) $ventaId, 8, '0', STR_PAD_LEFT)
            .strtoupper(substr(md5('ixe-venta-'.$ventaId), 0, 6));

        return $raw;
    }

    private function notificarCambioEstado(Venta $venta): void
    {
        try {
            $estado = strtolower(trim((string) $venta->estado));
            match ($estado) {
                'pago_acreditado' => $this->notificaciones->pagoAcreditado($venta),
                'en_curso' => $this->notificaciones->pedidoEnCurso($venta),
                'entregado' => $this->notificaciones->pedidoEntregado($venta),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Notificación de cambio de estado falló: '.$e->getMessage());
        }
    }
}
