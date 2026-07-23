<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    /** @use HasFactory<\Database\Factories\VentaFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forma_pago_id',
        'tienda_id',
        'total',
        'estado',
        'metodo_pago',
        'codigo_barras',
        'auto_complete_at',
        'next_state_at',
        'admin_nota',
        'admin_user_id',
        'admin_accion_at',
    ];

    protected $casts = [
        'total' => 'float',
        'auto_complete_at' => 'datetime',
        'next_state_at' => 'datetime',
        'admin_accion_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function forma_pago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function detalle_ventas()
    {
        return $this->hasMany(DetalleVenta::class);
    }
    public function detalle_inventarios()
    {
        return $this->hasMany(DetalleInventario::class);
    }
    public function envio()
    {
        return $this->hasOne(Envio::class);
    }
    public function cuponCanjeados()
    {
        return $this->hasMany(CuponCanjeado::class);
    }

    /** Administrador que ejecutó la última acción delicada (cancelar / devolver). */
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Estados que NO cuentan como ingreso de dinero en totales.
     * - cancelada: nunca se cobró o se anuló antes de completar.
     * - devuelto: la devolución ya finalizó → el monto se descuenta de totales.
     *
     * “devolucion_en_proceso” SÍ cuenta todavía (el dinero aún no se descuenta
     * hasta pasar a “devuelto”).
     */
    public const ESTADOS_EXCLUIDOS_INGRESO = [
        'cancelada',
        'cancelado',
        'devuelto',
    ];

    public static function estadoCuentaComoIngreso(?string $estado): bool
    {
        $key = strtolower(trim((string) $estado));

        return $key !== '' && ! in_array($key, self::ESTADOS_EXCLUIDOS_INGRESO, true);
    }

    /**
     * Restringe un query builder a ventas que suman dinero (ingreso válido).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function scopeSoloIngresoValido($query)
    {
        return $query->whereRaw(
            "LOWER(TRIM(COALESCE(estado, ''))) NOT IN ('cancelada', 'cancelado', 'devuelto')"
        );
    }
}
