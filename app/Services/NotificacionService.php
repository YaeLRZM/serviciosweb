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
        $metodo = strtolower(trim((string) ($venta->metodo_pago ?? '')));

        if ($metodo === 'efectivo') {
            Notificacion::create([
                'user_id' => (int) $venta->user_id,
                'tipo' => 'compra_efectivo_solicitud',
                'titulo' => 'Solicitud enviada',
                'mensaje' => "Tu solicitud de pago en efectivo #{$venta->id} fue enviada al vendedor.",
                'data' => [
                    'venta_id' => (int) $venta->id,
                    'estado' => (string) $venta->estado,
                    'metodo_pago' => 'efectivo',
                ],
            ]);
            $this->solicitudEfectivoVendedor($venta);

            return;
        }

        if ($metodo === 'tarjeta') {
            Notificacion::create([
                'user_id' => (int) $venta->user_id,
                'tipo' => 'pago_acreditado',
                'titulo' => 'Pago acreditado',
                'mensaje' => "Tu pago de la compra #{$venta->id} fue acreditado.",
                'data' => [
                    'venta_id' => (int) $venta->id,
                    'estado' => (string) $venta->estado,
                    'metodo_pago' => 'tarjeta',
                ],
            ]);
            $this->notificarVendedorVenta($venta, pendiente: false);

            return;
        }

        // Legacy sin metodo_pago.
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

        $this->notificarVendedorVenta($venta, pendiente: true);
    }

    /**
     * @deprecated Preferir pedidoEntregado. Se mantiene por si algún caller legado lo invoca.
     */
    public function compraCompletada(Venta $venta): void
    {
        $this->pedidoEntregado($venta);
    }

    public function pagoAcreditado(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'pago_acreditado',
            'titulo' => 'Pago acreditado',
            'mensaje' => "Tu pago de la compra #{$venta->id} fue acreditado.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => (string) $venta->estado,
            ],
        ]);
    }

    public function pedidoEnCurso(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'pedido_en_curso',
            'titulo' => 'Pedido en curso',
            'mensaje' => "Tu pedido #{$venta->id} está en curso.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => (string) $venta->estado,
            ],
        ]);
    }

    public function pedidoEntregado(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'pedido_entregado',
            'titulo' => 'Pedido entregado',
            'mensaje' => "Tu pedido #{$venta->id} fue entregado.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => (string) $venta->estado,
            ],
        ]);
        $this->notificarVendedorVenta($venta, pendiente: false);
    }

    public function efectivoActivadoComprador(Venta $venta): void
    {
        Notificacion::create([
            'user_id' => (int) $venta->user_id,
            'tipo' => 'efectivo_activado',
            'titulo' => 'Ya puedes pagar',
            'mensaje' => "El vendedor activó tu pago en efectivo de la compra #{$venta->id}. Ya puedes realizar tu pago.",
            'data' => [
                'venta_id' => (int) $venta->id,
                'estado' => (string) $venta->estado,
                'codigo_barras' => $venta->codigo_barras,
            ],
        ]);
    }

    public function solicitudEfectivoVendedor(Venta $venta): void
    {
        try {
            $vendedorUserId = Vendedor::query()
                ->where('tienda_id', (int) $venta->tienda_id)
                ->value('user_id');
            if (! $vendedorUserId) {
                return;
            }
            if ((int) $vendedorUserId === (int) $venta->user_id) {
                return;
            }

            Notificacion::create([
                'user_id' => (int) $vendedorUserId,
                'tipo' => 'solicitud_efectivo',
                'titulo' => 'Solicitud de pago en efectivo',
                'mensaje' => "Nueva solicitud de pago en efectivo #{$venta->id}. Actívala para generar el código.",
                'data' => [
                    'venta_id' => (int) $venta->id,
                    'tienda_id' => (int) $venta->tienda_id,
                    'estado' => (string) $venta->estado,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar solicitud efectivo: '.$e->getMessage());
        }
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
                        'estado' => (string) $venta->estado,
                    ],
                ]);
            } else {
                Notificacion::create([
                    'user_id' => (int) $vendedorUserId,
                    'tipo' => 'venta_entregada',
                    'titulo' => 'Venta entregada',
                    'mensaje' => "La venta #{$venta->id} de {$articuloTxt} fue entregada.",
                    'data' => [
                        'venta_id' => (int) $venta->id,
                        'tienda_id' => (int) $venta->tienda_id,
                        'estado' => 'entregado',
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
