<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Notificacion;
use App\Models\User;
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
