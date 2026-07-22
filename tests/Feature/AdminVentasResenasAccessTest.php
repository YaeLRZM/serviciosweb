<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (['admin', 'user', 'vendedor'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('permite al administrador abrir ventas generales y reseñas', function () {
    $admin = User::factory()->create([
        'email' => 'admin.panel.'.uniqid().'@example.com',
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.ventas.index'))
        ->assertOk()
        ->assertSee('Ventas generales', false);

    $this->actingAs($admin)
        ->get(route('admin.resenas.index'))
        ->assertOk()
        ->assertSee('Reseñas', false);
});

it('bloquea a usuarios no administradores en ventas generales y reseñas', function () {
    $user = User::factory()->create([
        'email' => 'user.panel.'.uniqid().'@example.com',
    ]);
    $user->assignRole('user');

    // Spatie role middleware responde 403 o redirige según config; aceptamos ambos.
    $ventas = $this->actingAs($user)->get(route('admin.ventas.index'));
    expect(in_array($ventas->status(), [403, 302], true))->toBeTrue();

    $resenas = $this->actingAs($user)->get(route('admin.resenas.index'));
    expect(in_array($resenas->status(), [403, 302], true))->toBeTrue();
});

it('redirige a invitados al login en rutas admin', function () {
    $this->get(route('admin.ventas.index'))
        ->assertRedirect();

    $this->get(route('admin.resenas.index'))
        ->assertRedirect();
});
