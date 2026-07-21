<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Notificacion;
use App\Models\Resena;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Venta;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    public function compraPendiente(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'compra_pendiente',
            'titulo' => 'Compra en proceso',
            'mensaje' => "Tu compra #{$venta->id} se confirmará en menos de 5 minutos.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => 'pendiente',
            ],
        ]);

        // Aviso al vendedor dueño de la tienda (misma venta, otro user_id).
        $this->notificarVendedorVenta($venta, pendiente: true);
    }

    public function compraCompletada(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'compra_completada',
            'titulo' => 'Compra confirmada',
            'mensaje' => "Tu compra #{$venta->id} se ha confirmado.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => 'completada',
            ],
        ]);

        $this->notificarVendedorVenta($venta, pendiente: false);
    }

    /**
     * Notifica al usuario vendedor vinculado a la tienda de la venta.
     * Filtrado natural por user_id en GET /api/notificaciones.
     */
    public function notificarVendedorVenta(Venta $venta, bool $pendiente): void
    {
        try {
            $vendedorUserId = Vendedor::query()
                ->where('tienda_id', (int) $venta->tienda_id)
                ->value('user_id');

            if (! $vendedorUserId) {
                return;
            }

            // No notificar si el comprador es el mismo usuario (edge case).
            if ((int) $vendedorUserId === (int) $venta->user_id) {
                return;
            }

            $venta->loadMissing(['detalle_ventas.articulo:id,nombre']);
            $nombres = $venta->detalle_ventas
                ->map(fn ($d) => $d->articulo?->nombre)
                ->filter()
                ->unique()
                ->take(2)
                ->values();
            $articuloTxt = $nombres->isNotEmpty()
                ? $nombres->implode(', ').($venta->detalle_ventas->count() > 2 ? '…' : '')
                : 'tus productos';

            if ($pendiente) {
                Notificacion::create([
                    'user_id' => (int) $vendedorUserId,
                    'tipo' => 'venta_pendiente',
                    'titulo' => 'Nueva venta',
                    'mensaje' => "Nueva venta #{$venta->id} pendiente de {$articuloTxt}.",
                    'data' => [
                        'venta_id' => (int) $venta->id,
                        'tienda_id' => (int) $venta->tienda_id,
                        'estado' => 'pendiente',
                    ],
                ]);
            } else {
                Notificacion::create([
                    'user_id' => (int) $vendedorUserId,
                    'tipo' => 'venta_completada',
                    'titulo' => 'Venta completada',
                    'mensaje' => "La venta #{$venta->id} de {$articuloTxt} se ha completado.",
                    'data' => [
                        'venta_id' => (int) $venta->id,
                        'tienda_id' => (int) $venta->tienda_id,
                        'estado' => 'completada',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar venta al vendedor: '.$e->getMessage());
        }
    }

    /**
     * Avisa al vendedor cuando un comprador reseña un producto de su tienda.
     */
    public function nuevaResena(Resena $resena): void
    {
        try {
            $resena->loadMissing(['articulo:id,nombre,tienda_id']);
            $articulo = $resena->articulo;
            if (! $articulo || ! $articulo->tienda_id) {
                return;
            }

            $vendedorUserId = Vendedor::query()
                ->where('tienda_id', (int) $articulo->tienda_id)
                ->value('user_id');
            if (! $vendedorUserId) {
                return;
            }
            if ((int) $vendedorUserId === (int) $resena->user_id) {
                return;
            }

            $nombre = $articulo->nombre ?: ('Artículo #'.$articulo->id);
            $calif = (int) $resena->calificacion;

            Notificacion::create([
                'user_id' => (int) $vendedorUserId,
                'tipo' => 'nueva_resena',
                'titulo' => 'Nueva reseña',
                'mensaje' => "Nueva reseña ({$calif}★) en {$nombre}.",
                'data' => [
                    'resena_id' => (int) $resena->id,
                    'articulo_id' => (int) $articulo->id,
                    'tienda_id' => (int) $articulo->tienda_id,
                    'calificacion' => $calif,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar reseña al vendedor: '.$e->getMessage());
        }
    }

    /**
     * Avisa a compradores (rol user) de una nueva publicación de tienda.
     */
    public function nuevaPublicacion(Articulo $articulo): void
    {
        $articulo->loadMissing('tienda:id,nombre');
        $tiendaNombre = $articulo->tienda?->nombre
            ? (string) $articulo->tienda->nombre
            : 'una tienda';

        $titulo = 'Nueva publicación';
        $mensaje = "Nueva publicación de {$tiendaNombre}.";
        $data = [
            'articulo_id' => (int) $articulo->id,
            'tienda_id' => (int) $articulo->tienda_id,
            'tienda_nombre' => $tiendaNombre,
        ];

        try {
            $userIds = User::role('user')->pluck('id');
            foreach ($userIds as $userId) {
                Notificacion::create([
                    'user_id' => (int) $userId,
                    'tipo' => 'nueva_publicacion',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'data' => $data,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudieron crear notificaciones de publicación: '.$e->getMessage());
        }
    }
}
