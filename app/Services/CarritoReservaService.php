<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reservas de stock en carrito (5 minutos).
 * - Al agregar: descuenta stock y fija expires_at.
 * - Al quitar/vencer: devuelve stock.
 * - Al comprar: consume la línea sin devolver stock.
 */
class CarritoReservaService
{
    public const RESERVA_MINUTOS = 5;

    public function liberarVencidas(?int $soloUserId = null): int
    {
        $query = DetalleCarrito::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id');

        if ($soloUserId !== null) {
            $query->whereHas('carrito', fn ($q) => $q->where('user_id', $soloUserId));
        }

        $ids = $query->limit(300)->pluck('id');
        $n = 0;
        foreach ($ids as $id) {
            if ($this->liberarLineaPorId((int) $id, restaurarStock: true)) {
                $n++;
            }
        }

        return $n;
    }

    public function getOrCreateCarrito(User $user): Carrito
    {
        $carrito = Carrito::query()->where('user_id', (int) $user->id)->first();
        if ($carrito) {
            return $carrito;
        }

        return Carrito::create([
            'user_id' => (int) $user->id,
            'total' => 0,
        ]);
    }

    /**
     * Snapshot del carrito activo (tras liberar vencidos del usuario).
     *
     * @return array{items: list<array<string,mixed>>, liberados: int}
     */
    public function snapshot(User $user): array
    {
        $liberados = $this->liberarVencidas((int) $user->id);
        $carrito = Carrito::query()->where('user_id', (int) $user->id)->first();
        if (! $carrito) {
            return ['items' => [], 'liberados' => $liberados];
        }

        $lineas = DetalleCarrito::query()
            ->where('carrito_id', $carrito->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with(['articulo:id,nombre,precio,stock,disponible,tienda_id'])
            ->orderBy('id')
            ->get();

        $items = [];
        foreach ($lineas as $linea) {
            $art = $linea->articulo;
            $items[] = [
                'id' => (int) $linea->id,
                'articulo_id' => (int) $linea->articulo_id,
                'cantidad' => (int) $linea->cantidad,
                'precio_unitario' => (float) $linea->precio_unitario,
                'expires_at' => $linea->expires_at?->toIso8601String(),
                'articulo' => $art ? [
                    'id' => (int) $art->id,
                    'nombre' => $art->nombre,
                    'precio' => (float) $art->precio,
                    'stock' => (int) $art->stock,
                    'disponible' => (bool) $art->disponible,
                    'tienda_id' => (int) $art->tienda_id,
                ] : null,
            ];
        }

        return ['items' => $items, 'liberados' => $liberados];
    }

    public function agregar(User $user, int $articuloId, int $cantidad): array
    {
        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => ['La cantidad mínima es 1.'],
            ]);
        }

        return DB::transaction(function () use ($user, $articuloId, $cantidad) {
            $this->liberarVencidas((int) $user->id);

            /** @var Articulo|null $articulo */
            $articulo = Articulo::query()->whereKey($articuloId)->lockForUpdate()->first();
            if (! $articulo) {
                throw ValidationException::withMessages([
                    'articulo_id' => ['El artículo no existe.'],
                ]);
            }
            if (! $articulo->disponible) {
                throw ValidationException::withMessages([
                    'articulo_id' => ['Este artículo no está disponible.'],
                ]);
            }
            if ((int) $articulo->stock < $cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => [
                        "No hay suficiente stock para «{$articulo->nombre}» "
                        ."(disponible: {$articulo->stock}).",
                    ],
                ]);
            }

            $carrito = $this->getOrCreateCarrito($user);
            /** @var DetalleCarrito|null $linea */
            $linea = DetalleCarrito::query()
                ->where('carrito_id', $carrito->id)
                ->where('articulo_id', $articuloId)
                ->lockForUpdate()
                ->first();

            $expires = now()->addMinutes(self::RESERVA_MINUTOS);

            if ($linea) {
                // Si la línea estaba vencida ya se liberó; si está activa, sumamos.
                $linea->cantidad = (int) $linea->cantidad + $cantidad;
                $linea->precio_unitario = (float) $articulo->precio;
                $linea->expires_at = $expires;
                $linea->save();
            } else {
                $linea = DetalleCarrito::create([
                    'carrito_id' => $carrito->id,
                    'articulo_id' => $articuloId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => (float) $articulo->precio,
                    'expires_at' => $expires,
                ]);
            }

            $articulo->stock = (int) $articulo->stock - $cantidad;
            if ((int) $articulo->stock <= 0) {
                $articulo->disponible = false;
            }
            $articulo->save();

            return $this->snapshot($user);
        });
    }

    public function actualizarCantidad(User $user, int $articuloId, int $nuevaCantidad): array
    {
        return DB::transaction(function () use ($user, $articuloId, $nuevaCantidad) {
            $this->liberarVencidas((int) $user->id);

            $carrito = Carrito::query()->where('user_id', (int) $user->id)->lockForUpdate()->first();
            if (! $carrito) {
                throw ValidationException::withMessages([
                    'carrito' => ['No tienes artículos en el carrito.'],
                ]);
            }

            /** @var DetalleCarrito|null $linea */
            $linea = DetalleCarrito::query()
                ->where('carrito_id', $carrito->id)
                ->where('articulo_id', $articuloId)
                ->lockForUpdate()
                ->first();

            if (! $linea) {
                throw ValidationException::withMessages([
                    'articulo_id' => ['Ese artículo no está en tu carrito.'],
                ]);
            }

            if ($nuevaCantidad <= 0) {
                $this->liberarLinea($linea, restaurarStock: true);

                return $this->snapshot($user);
            }

            /** @var Articulo $articulo */
            $articulo = Articulo::query()->whereKey($articuloId)->lockForUpdate()->firstOrFail();
            $actual = (int) $linea->cantidad;
            $delta = $nuevaCantidad - $actual;

            if ($delta > 0) {
                if ((int) $articulo->stock < $delta) {
                    throw ValidationException::withMessages([
                        'cantidad' => [
                            "No hay suficiente stock (disponible: {$articulo->stock}).",
                        ],
                    ]);
                }
                $articulo->stock = (int) $articulo->stock - $delta;
                if ((int) $articulo->stock <= 0) {
                    $articulo->disponible = false;
                }
            } elseif ($delta < 0) {
                $articulo->stock = (int) $articulo->stock + abs($delta);
                if ((int) $articulo->stock > 0) {
                    $articulo->disponible = true;
                }
            }
            $articulo->save();

            $linea->cantidad = $nuevaCantidad;
            $linea->expires_at = now()->addMinutes(self::RESERVA_MINUTOS);
            $linea->precio_unitario = (float) $articulo->precio;
            $linea->save();

            return $this->snapshot($user);
        });
    }

    public function quitar(User $user, int $articuloId): array
    {
        return DB::transaction(function () use ($user, $articuloId) {
            $this->liberarVencidas((int) $user->id);

            $carrito = Carrito::query()->where('user_id', (int) $user->id)->first();
            if (! $carrito) {
                return $this->snapshot($user);
            }

            $linea = DetalleCarrito::query()
                ->where('carrito_id', $carrito->id)
                ->where('articulo_id', $articuloId)
                ->lockForUpdate()
                ->first();

            if ($linea) {
                $this->liberarLinea($linea, restaurarStock: true);
            }

            return $this->snapshot($user);
        });
    }

    public function vaciar(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $this->liberarVencidas((int) $user->id);
            $carrito = Carrito::query()->where('user_id', (int) $user->id)->first();
            if ($carrito) {
                $lineas = DetalleCarrito::query()
                    ->where('carrito_id', $carrito->id)
                    ->lockForUpdate()
                    ->get();
                foreach ($lineas as $linea) {
                    $this->liberarLinea($linea, restaurarStock: true);
                }
            }

            return $this->snapshot($user);
        });
    }

    /**
     * Consume reservas activas al comprar (sin devolver stock).
     * Si no hay reserva suficiente, descuenta del stock libre (fallback).
     *
     * @param  array<int,int>  $qtyByArticulo
     */
    public function consumirReservasAlComprar(User $user, array $qtyByArticulo): void
    {
        $this->liberarVencidas((int) $user->id);

        $carrito = Carrito::query()->where('user_id', (int) $user->id)->lockForUpdate()->first();

        foreach ($qtyByArticulo as $articuloId => $cantidad) {
            $articuloId = (int) $articuloId;
            $cantidad = (int) $cantidad;

            /** @var Articulo $articulo */
            $articulo = Articulo::query()->whereKey($articuloId)->lockForUpdate()->firstOrFail();

            $reservado = 0;
            if ($carrito) {
                $linea = DetalleCarrito::query()
                    ->where('carrito_id', $carrito->id)
                    ->where('articulo_id', $articuloId)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->lockForUpdate()
                    ->first();

                if ($linea) {
                    $reservado = (int) $linea->cantidad;
                    // Consume la línea completa sin restaurar stock (ya estaba descontado).
                    $linea->delete();
                }
            }

            if ($reservado >= $cantidad) {
                // Reserva cubre la compra; si sobra reserva, devolver el exceso.
                $exceso = $reservado - $cantidad;
                if ($exceso > 0) {
                    $articulo->stock = (int) $articulo->stock + $exceso;
                    if ((int) $articulo->stock > 0) {
                        $articulo->disponible = true;
                    }
                    $articulo->save();
                }
                // Stock de $cantidad ya estaba fuera del inventario disponible.
                continue;
            }

            // Reserva insuficiente: descontar el faltante del stock libre.
            $faltante = $cantidad - $reservado;
            if ((int) $articulo->stock < $faltante) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stock insuficiente para «{$articulo->nombre}» "
                        ."(disponible: {$articulo->stock}, solicitado: {$faltante}).",
                    ],
                ]);
            }
            $articulo->stock = (int) $articulo->stock - $faltante;
            if ((int) $articulo->stock <= 0) {
                $articulo->disponible = false;
            }
            $articulo->save();
        }
    }

    private function liberarLineaPorId(int $id, bool $restaurarStock): bool
    {
        return (bool) DB::transaction(function () use ($id, $restaurarStock) {
            $linea = DetalleCarrito::query()->whereKey($id)->lockForUpdate()->first();
            if (! $linea) {
                return false;
            }
            // Re-check expiry under lock
            if ($linea->expires_at && $linea->expires_at->isFuture()) {
                return false;
            }
            $this->liberarLinea($linea, $restaurarStock);

            return true;
        });
    }

    private function liberarLinea(DetalleCarrito $linea, bool $restaurarStock): void
    {
        if ($restaurarStock) {
            $articulo = Articulo::query()
                ->whereKey((int) $linea->articulo_id)
                ->lockForUpdate()
                ->first();
            if ($articulo) {
                $articulo->stock = (int) $articulo->stock + (int) $linea->cantidad;
                if ((int) $articulo->stock > 0) {
                    $articulo->disponible = true;
                }
                $articulo->save();
            }
        }
        $linea->delete();
    }
}
