<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Completa compras pendientes cuyo auto_complete_at ya venció.
 * Llamado por comando programado y al listar/ver ventas o notificaciones.
 */
class VentaAutoCompleteService
{
    /** Minutos de confirmación automática (misma regla que VentaController@store). */
    public const MINUTOS_AUTO_COMPLETE = 5;

    public function __construct(
        private readonly NotificacionService $notificaciones,
    ) {}

    /**
     * @return int número de ventas actualizadas
     */
    public function completarVencidas(): int
    {
        // Ventas antiguas / inconsistentes: sin auto_complete_at no hay contador ni cierre.
        $this->asegurarAutoCompleteAtPendientes();

        $ids = Venta::query()
            ->where('estado', 'pendiente')
            ->whereNotNull('auto_complete_at')
            ->where('auto_complete_at', '<=', now())
            ->orderBy('id')
            ->limit(200)
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

                    $venta->estado = 'completada';
                    $venta->save();

                    return $venta;
                });

                if ($updated) {
                    $this->notificaciones->compraCompletada($updated);
                    $count++;
                }
            } catch (\Throwable $e) {
                Log::warning("No se pudo auto-completar venta {$id}: ".$e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Rellena auto_complete_at faltante en pendientes (created_at + 5 min).
     * Así comprador y vendedor ven el mismo contador y el cierre automático funciona.
     */
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
}
