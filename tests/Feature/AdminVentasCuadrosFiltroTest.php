<?php

use App\Models\FormaPago;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Venta;
use App\Services\Admin\VentasGeneralesDataService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('user', 'web');
});

function ventaParaCuadro(string $estado, float $total, User $cliente, Tienda $tienda, FormaPago $forma): Venta
{
    return Venta::query()->create([
        'user_id' => $cliente->id,
        'forma_pago_id' => $forma->id,
        'tienda_id' => $tienda->id,
        'total' => $total,
        'estado' => $estado,
        'metodo_pago' => 'tarjeta',
    ]);
}

it('resumen de cuadros distingue ventas, devoluciones y monto', function () {
    $cliente = User::factory()->create(['email' => 'cuadro.'.uniqid().'@ex.com']);
    $cliente->assignRole('user');

    $tienda = Tienda::query()->first() ?? Tienda::query()->create([
        'nombre' => 'Tienda Cuadros '.uniqid(),
        'rfc_moral' => 'CUE'.rand(100000000, 999999999),
        'descripcion' => 'Test',
    ]);

    $forma = FormaPago::query()->first();
    if (! $forma) {
        $forma = new FormaPago;
        $forma->nombre = 'Tarjeta test';
        $forma->save();
    }

    // Aislar por fecha futura rara no es práctico; filtramos por cliente_id.
    ventaParaCuadro('entregado', 100, $cliente, $tienda, $forma);
    ventaParaCuadro('en_curso', 50, $cliente, $tienda, $forma);
    ventaParaCuadro('cancelada', 80, $cliente, $tienda, $forma);
    ventaParaCuadro('devolucion_en_proceso', 40, $cliente, $tienda, $forma);
    ventaParaCuadro('devuelto', 30, $cliente, $tienda, $forma);

    $svc = app(VentasGeneralesDataService::class);
    $filtros = ['cliente_id' => (string) $cliente->id];
    $resumen = $svc->resumen($filtros);

    // Ventas = no canceladas ni devoluciones → entregado + en_curso = 2
    expect($resumen['ventas'])->toBe(2);
    // Entregadas
    expect($resumen['entregadas'])->toBe(1);
    // En proceso (solo flujo de pago)
    expect($resumen['en_proceso'])->toBe(1);
    // Canceladas
    expect($resumen['canceladas'])->toBe(1);
    // Devoluciones = en proceso + devuelto
    expect($resumen['devoluciones'])->toBe(2);
    // Monto: excluye cancelada (80) y devuelto (30); incluye en_curso, entregado y en devolución
    // 100 + 50 + 40 = 190
    expect($resumen['monto_total'])->toBe(190.0);
});

it('filtro grupo devoluciones y ventas coincide con el conteo del cuadro', function () {
    $cliente = User::factory()->create(['email' => 'grupo.'.uniqid().'@ex.com']);
    $cliente->assignRole('user');

    $tienda = Tienda::query()->first() ?? Tienda::query()->create([
        'nombre' => 'Tienda Grupo '.uniqid(),
        'rfc_moral' => 'GRP'.rand(100000000, 999999999),
        'descripcion' => 'Test',
    ]);

    $forma = FormaPago::query()->first();
    if (! $forma) {
        $forma = new FormaPago;
        $forma->nombre = 'Tarjeta test';
        $forma->save();
    }

    ventaParaCuadro('entregado', 100, $cliente, $tienda, $forma);
    ventaParaCuadro('cancelada', 80, $cliente, $tienda, $forma);
    ventaParaCuadro('devolucion_en_proceso', 40, $cliente, $tienda, $forma);
    ventaParaCuadro('devuelto', 30, $cliente, $tienda, $forma);

    $svc = app(VentasGeneralesDataService::class);
    $base = ['cliente_id' => (string) $cliente->id];
    $resumen = $svc->resumen($base);

    $nVentas = $svc->baseQuery(array_merge($base, ['grupo' => 'ventas']))->count();
    $nDev = $svc->baseQuery(array_merge($base, ['grupo' => 'devoluciones']))->count();
    $nCancel = $svc->baseQuery(array_merge($base, ['grupo' => 'canceladas']))->count();
    $nEnt = $svc->baseQuery(array_merge($base, ['grupo' => 'entregadas']))->count();
    $nMonto = $svc->baseQuery(array_merge($base, ['grupo' => 'monto']))->count();

    expect($nVentas)->toBe($resumen['ventas']);
    expect($nDev)->toBe($resumen['devoluciones']);
    expect($nCancel)->toBe($resumen['canceladas']);
    expect($nEnt)->toBe($resumen['entregadas']);
    // Monto: entregado + devolucion_en_proceso (no cancelada ni devuelto) = 2
    expect($nMonto)->toBe(2);
});
