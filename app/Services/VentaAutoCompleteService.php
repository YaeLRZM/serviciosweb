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
    public function __construct(
        private readonly NotificacionService $notificaciones,
    ) {}

    /**
     * @return int número de ventas actualizadas
     */
    public function completarVencidas(): int
    {
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
}
