<?php

namespace App\Services\Admin;

use App\Models\Articulo;
use App\Models\Notificacion;
use App\Models\Resena;
use App\Models\User;
use App\Models\Venta;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Feed de la campana del administrador.
 *
 * Combina:
 * - notificaciones propias del admin (si existen en BD);
 * - eventos recientes del sistema (compras, cancelaciones, reseñas, publicaciones).
 *
 * El estado leído de eventos derivados se guarda en sesión (sin inventar filas).
 */
class AdminNotificationFeedService
{
    public const SESSION_LEIDAS = 'admin_notif_leidas';

    /**
     * @return array{
     *   items: list<array<string,mixed>>,
     *   no_leidas: int,
     *   total: int
     * }
     */
    public function feed(User $admin, int $limit = 25): array
    {
        $items = [];

        // 1) Notificaciones reales dirigidas al admin.
        if (Schema::hasTable('notificaciones')) {
            $dbItems = Notificacion::query()
                ->where('user_id', (int) $admin->id)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            foreach ($dbItems as $n) {
                $items[] = $this->mapDbNotificacion($n);
            }
        }

        // 2) Eventos del sistema relevantes para supervisión.
        $items = array_merge($items, $this->eventosRecientes());

        // 3) Alertas operativas actuales (complemento, no relleno).
        try {
            foreach (app(DashboardStatsService::class)->alertasOperativas() as $alerta) {
                $items[] = [
                    'id' => 'alerta-'.$alerta['id'],
                    'origen' => 'alerta',
                    'titulo' => $alerta['tipo'],
                    'mensaje' => $alerta['motivo'],
                    'etiqueta' => $alerta['etiqueta'] ?? 'Alerta',
                    'url' => $alerta['url'] ?? route('admin.dashboard'),
                    'urgente' => (bool) ($alerta['urgente'] ?? false),
                    'fecha' => now()->toIso8601String(),
                    'fecha_label' => 'Ahora',
                    'leida' => $this->esLeida('alerta-'.$alerta['id']),
                ];
            }
        } catch (\Throwable) {
            // No bloquear la campana si fallan las alertas.
        }

        // Deduplicar por id, ordenar por fecha desc.
        $byId = [];
        foreach ($items as $item) {
            $byId[$item['id']] = $item;
        }
        $items = array_values($byId);

        usort($items, function ($a, $b) {
            if (($a['urgente'] ?? false) !== ($b['urgente'] ?? false)) {
                return ($b['urgente'] ?? false) <=> ($a['urgente'] ?? false);
            }
            // No leídas primero
            if (($a['leida'] ?? false) !== ($b['leida'] ?? false)) {
                return ($a['leida'] ? 1 : 0) <=> ($b['leida'] ? 1 : 0);
            }

            return strcmp((string) ($b['fecha'] ?? ''), (string) ($a['fecha'] ?? ''));
        });

        $items = array_slice($items, 0, $limit);
        $noLeidas = collect($items)->where('leida', false)->count();

        return [
            'items' => $items,
            'no_leidas' => $noLeidas,
            'total' => count($items),
        ];
    }

    public function marcarLeida(string $id, ?User $admin = null): void
    {
        if (str_starts_with($id, 'db-') && $admin) {
            $realId = (int) substr($id, 3);
            Notificacion::query()
                ->where('id', $realId)
                ->where('user_id', (int) $admin->id)
                ->whereNull('leida_at')
                ->update(['leida_at' => now()]);
        }

        $leidas = session()->get(self::SESSION_LEIDAS, []);
        if (! is_array($leidas)) {
            $leidas = [];
        }
        $leidas[$id] = now()->toIso8601String();
        // Limitar tamaño de sesión
        if (count($leidas) > 200) {
            $leidas = array_slice($leidas, -150, null, true);
        }
        session()->put(self::SESSION_LEIDAS, $leidas);
    }

    public function marcarTodas(User $admin): void
    {
        if (Schema::hasTable('notificaciones')) {
            Notificacion::query()
                ->where('user_id', (int) $admin->id)
                ->whereNull('leida_at')
                ->update(['leida_at' => now()]);
        }

        $feed = $this->feed($admin, 50);
        $leidas = session()->get(self::SESSION_LEIDAS, []);
        if (! is_array($leidas)) {
            $leidas = [];
        }
        foreach ($feed['items'] as $item) {
            $leidas[$item['id']] = now()->toIso8601String();
        }
        session()->put(self::SESSION_LEIDAS, $leidas);
    }

    /**
     * @return list<array<string,mixed>>
     */
    protected function eventosRecientes(): array
    {
        $items = [];
        $desde = now()->subDays(14);

        // Compras nuevas
        $ventas = Venta::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno', 'tienda:id,nombre'])
            ->where('created_at', '>=', $desde)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        foreach ($ventas as $v) {
            $estado = strtolower(trim((string) $v->estado));
            $esCancelada = $estado === 'cancelada';
            $ref = 'CMP-'.str_pad((string) $v->id, 5, '0', STR_PAD_LEFT);
            $cliente = $v->user?->nombre_completo ?: 'un cliente';
            $tienda = $v->tienda?->nombre ?: 'una tienda';

            if ($esCancelada) {
                $items[] = [
                    'id' => 'evt-cancel-'.$v->id,
                    'origen' => 'evento',
                    'titulo' => 'Compra cancelada',
                    'mensaje' => "La compra {$ref} de {$cliente} en {$tienda} fue cancelada.",
                    'etiqueta' => 'Compra',
                    'url' => route('admin.ventas.index', ['busqueda' => (string) $v->id, 'estado' => 'cancelada']),
                    'urgente' => true,
                    'fecha' => optional($v->created_at)?->toIso8601String() ?? now()->toIso8601String(),
                    'fecha_label' => $this->fechaLabel($v->created_at),
                    'leida' => $this->esLeida('evt-cancel-'.$v->id),
                ];
            } else {
                $items[] = [
                    'id' => 'evt-venta-'.$v->id,
                    'origen' => 'evento',
                    'titulo' => 'Nueva compra registrada',
                    'mensaje' => "{$cliente} compró en {$tienda} ({$ref}) por \$".number_format((float) $v->total, 2).'.',
                    'etiqueta' => 'Compra',
                    'url' => route('admin.ventas.index', ['busqueda' => (string) $v->id]),
                    'urgente' => false,
                    'fecha' => optional($v->created_at)?->toIso8601String() ?? now()->toIso8601String(),
                    'fecha_label' => $this->fechaLabel($v->created_at),
                    'leida' => $this->esLeida('evt-venta-'.$v->id),
                ];
            }
        }

        // Reseñas recientes
        $resenas = Resena::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno', 'articulo:id,nombre'])
            ->where('created_at', '>=', $desde)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        foreach ($resenas as $r) {
            $baja = (int) $r->calificacion <= 2;
            $autor = $r->user?->nombre_completo ?: 'Un cliente';
            $prenda = $r->articulo?->nombre ?: 'una prenda';
            $id = 'evt-resena-'.$r->id;

            $items[] = [
                'id' => $id,
                'origen' => 'evento',
                'titulo' => $baja ? 'Reseña con baja calificación' : 'Nueva reseña',
                'mensaje' => $baja
                    ? "{$autor} dejó {$r->calificacion}/5 en “{$prenda}”."
                    : "{$autor} valoró “{$prenda}” con {$r->calificacion}/5.",
                'etiqueta' => 'Reseña',
                'url' => route('admin.resenas.index', [
                    'busqueda' => $prenda,
                    'calificacion' => $baja ? 'baja' : '',
                ]),
                'urgente' => $baja,
                'fecha' => optional($r->created_at)?->toIso8601String() ?? now()->toIso8601String(),
                'fecha_label' => $this->fechaLabel($r->created_at),
                'leida' => $this->esLeida($id),
            ];
        }

        // Publicaciones nuevas
        $articulos = Articulo::query()
            ->with(['tienda:id,nombre'])
            ->where('created_at', '>=', $desde)
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        foreach ($articulos as $a) {
            $id = 'evt-articulo-'.$a->id;
            $items[] = [
                'id' => $id,
                'origen' => 'evento',
                'titulo' => 'Nueva publicación',
                'mensaje' => 'Se publicó “'.($a->nombre ?: 'prenda').'”'.($a->tienda?->nombre ? ' en '.$a->tienda->nombre : '').'.',
                'etiqueta' => 'Publicación',
                'url' => route('admin.publicacion.index', ['busqueda' => $a->nombre ?: (string) $a->id]),
                'urgente' => false,
                'fecha' => optional($a->created_at)?->toIso8601String() ?? now()->toIso8601String(),
                'fecha_label' => $this->fechaLabel($a->created_at),
                'leida' => $this->esLeida($id),
            ];
        }

        return $items;
    }

    protected function mapDbNotificacion(Notificacion $n): array
    {
        $id = 'db-'.$n->id;
        $data = is_array($n->data) ? $n->data : [];
        $url = $this->urlDesdeData($n->tipo, $data);

        return [
            'id' => $id,
            'origen' => 'bd',
            'titulo' => (string) $n->titulo,
            'mensaje' => (string) $n->mensaje,
            'etiqueta' => $this->etiquetaTipo($n->tipo),
            'url' => $url,
            'urgente' => in_array($n->tipo, ['solicitud_efectivo', 'venta_pendiente'], true),
            'fecha' => optional($n->created_at)?->toIso8601String() ?? now()->toIso8601String(),
            'fecha_label' => $this->fechaLabel($n->created_at),
            'leida' => $n->leida_at !== null || $this->esLeida($id),
        ];
    }

    protected function urlDesdeData(string $tipo, array $data): string
    {
        if (! empty($data['venta_id'])) {
            return route('admin.ventas.index', ['busqueda' => (string) $data['venta_id']]);
        }
        if (! empty($data['articulo_id'])) {
            return route('admin.publicacion.index', ['busqueda' => (string) $data['articulo_id']]);
        }
        if (str_contains($tipo, 'resena')) {
            return route('admin.resenas.index');
        }
        if (str_contains($tipo, 'venta') || str_contains($tipo, 'compra') || str_contains($tipo, 'pedido') || str_contains($tipo, 'pago')) {
            return route('admin.ventas.index');
        }

        return route('admin.dashboard');
    }

    protected function etiquetaTipo(string $tipo): string
    {
        return match (true) {
            str_contains($tipo, 'resena') => 'Reseña',
            str_contains($tipo, 'publicacion') => 'Publicación',
            str_contains($tipo, 'venta'), str_contains($tipo, 'compra'), str_contains($tipo, 'pedido'), str_contains($tipo, 'pago'), str_contains($tipo, 'efectivo') => 'Compra',
            default => 'Aviso',
        };
    }

    protected function esLeida(string $id): bool
    {
        $leidas = session()->get(self::SESSION_LEIDAS, []);

        return is_array($leidas) && array_key_exists($id, $leidas);
    }

    protected function fechaLabel(?Carbon $fecha): string
    {
        if (! $fecha) {
            return '';
        }
        if ($fecha->isToday()) {
            return 'Hoy '.$fecha->format('H:i');
        }
        if ($fecha->isYesterday()) {
            return 'Ayer '.$fecha->format('H:i');
        }

        return $fecha->format('d/m/Y H:i');
    }
}
