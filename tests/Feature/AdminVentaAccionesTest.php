<?php

use App\Models\Articulo;
use App\Models\DetalleVenta;
use App\Models\FormaPago;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Venta;
use App\Services\Admin\AdminVentaAccionesService;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('user', 'web');
});

function crearVentaPrueba(string $estado, User $cliente): Venta
{
    $tienda = Tienda::query()->first() ?? Tienda::query()->create([
        'nombre' => 'Tienda Test '.uniqid(),
        'rfc_moral' => 'TST'.rand(100000000, 999999999),
        'descripcion' => 'Test',
    ]);

    $forma = FormaPago::query()->first();
    if (! $forma) {
        $forma = new FormaPago;
        $forma->nombre = 'Prueba';
        $forma->save();
    }

    $venta = Venta::query()->create([
        'user_id' => $cliente->id,
        'forma_pago_id' => $forma->id,
        'tienda_id' => $tienda->id,
        'total' => 100,
        'estado' => $estado,
        'metodo_pago' => 'tarjeta',
    ]);

    $articulo = Articulo::query()->first();
    if ($articulo) {
        DetalleVenta::query()->create([
            'venta_id' => $venta->id,
            'articulo_id' => $articulo->id,
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100,
        ]);
    }

    return $venta;
}

it('solo el administrador puede cancelar o devolver', function () {
    $user = User::factory()->create(['email' => 'u.'.uniqid().'@ex.com']);
    $user->assignRole('user');
    $admin = User::factory()->create(['email' => 'a.'.uniqid().'@ex.com']);
    $admin->assignRole('admin');

    $venta = crearVentaPrueba('pendiente', $user);
    $svc = app(AdminVentaAccionesService::class);

    expect(fn () => $svc->cancelar($user, $venta->id))
        ->toThrow(InvalidArgumentException::class);

    $svc->cancelar($admin, $venta->id);
    expect($venta->fresh()->estado)->toBe('cancelada');
    expect($venta->fresh()->admin_user_id)->toBe($admin->id);
});

it('no cancela ventas entregadas (usar devolucion)', function () {
    $cliente = User::factory()->create(['email' => 'c.'.uniqid().'@ex.com']);
    $cliente->assignRole('user');
    $admin = User::factory()->create(['email' => 'a2.'.uniqid().'@ex.com']);
    $admin->assignRole('admin');

    $venta = crearVentaPrueba('entregado', $cliente);
    $svc = app(AdminVentaAccionesService::class);

    expect(fn () => $svc->cancelar($admin, $venta->id))
        ->toThrow(InvalidArgumentException::class);

    expect($venta->fresh()->estado)->toBe('entregado');
});

it('inicia devolucion y completa tras el plazo', function () {
    $cliente = User::factory()->create(['email' => 'c2.'.uniqid().'@ex.com']);
    $cliente->assignRole('user');
    $admin = User::factory()->create(['email' => 'a3.'.uniqid().'@ex.com']);
    $admin->assignRole('admin');

    $venta = crearVentaPrueba('entregado', $cliente);
    $svc = app(AdminVentaAccionesService::class);

    $v = $svc->iniciarDevolucion($admin, $venta->id);
    expect($v->estado)->toBe('devolucion_en_proceso');
    expect($v->next_state_at)->not->toBeNull();
    expect($v->admin_nota)->toContain('Devolución');

    // No duplicar
    expect(fn () => $svc->iniciarDevolucion($admin, $venta->id))
        ->toThrow(InvalidArgumentException::class);

    // Simular vencimiento de 2 min
    $v->next_state_at = now()->subMinute();
    $v->save();

    $n = $svc->completarDevolucionesVencidas();
    expect($n)->toBeGreaterThanOrEqual(1);
    expect($venta->fresh()->estado)->toBe('devuelto');

    // No devolver de nuevo
    expect(fn () => $svc->iniciarDevolucion($admin, $venta->id))
        ->toThrow(InvalidArgumentException::class);
});

it('excluye canceladas y devueltas del monto de ingreso', function () {
    $cliente = User::factory()->create(['email' => 'c-sum.'.uniqid().'@ex.com']);
    $cliente->assignRole('user');
    $admin = User::factory()->create(['email' => 'a-sum.'.uniqid().'@ex.com']);
    $admin->assignRole('admin');

    $ok = crearVentaPrueba('entregado', $cliente);
    $ok->total = 200;
    $ok->save();

    $cancel = crearVentaPrueba('cancelada', $cliente);
    $cancel->total = 50;
    $cancel->save();

    $dev = crearVentaPrueba('devuelto', $cliente);
    $dev->total = 75;
    $dev->save();

    $enDev = crearVentaPrueba('devolucion_en_proceso', $cliente);
    $enDev->total = 30;
    $enDev->save();

    $resumen = app(\App\Services\Admin\VentasGeneralesDataService::class)->resumen([]);
    // Debe incluir entregado + en devolución; no cancelada ni devuelto.
    expect($resumen['monto_total'])->toBeGreaterThanOrEqual(230.0);

    $ids = [$ok->id, $cancel->id, $dev->id, $enDev->id];
    $subset = App\Models\Venta::query()->whereIn('id', $ids);
    $sumaValida = (float) (clone $subset)->soloIngresoValido()->sum('total');
    expect($sumaValida)->toBe(230.0); // 200 + 30
    expect(App\Models\Venta::estadoCuentaComoIngreso('cancelada'))->toBeFalse();
    expect(App\Models\Venta::estadoCuentaComoIngreso('devuelto'))->toBeFalse();
    expect(App\Models\Venta::estadoCuentaComoIngreso('devolucion_en_proceso'))->toBeTrue();
    expect(App\Models\Venta::estadoCuentaComoIngreso('entregado'))->toBeTrue();
});

it('la vista de ventas admin solo es accesible para admin', function () {
    $user = User::factory()->create(['email' => 'u2.'.uniqid().'@ex.com']);
    $user->assignRole('user');

    $this->actingAs($user)
        ->get(route('admin.ventas.index'))
        ->assertForbidden();

    $admin = User::factory()->create(['email' => 'a4.'.uniqid().'@ex.com']);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.ventas.index'))
        ->assertOk()
        ->assertSee('Ventas generales', false);
});
