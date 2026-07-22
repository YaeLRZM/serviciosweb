<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('user', 'web');
});

it('muestra el perfil del administrador con los formularios nuevos', function () {
    $admin = User::factory()->create([
        'email' => 'admin.profile.'.uniqid().'@example.com',
        'nombre' => 'Admin',
        'apellido_paterno' => 'Ixé',
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.profile'))
        ->assertOk()
        ->assertSee('Mi perfil', false)
        ->assertSeeVolt('admin.profile.info-form')
        ->assertSeeVolt('admin.profile.password-form')
        ->assertSeeVolt('admin.profile.delete-form');
});

it('permite actualizar datos reales del perfil', function () {
    $admin = User::factory()->create([
        'email' => 'admin.upd.'.uniqid().'@example.com',
        'nombre' => 'Antes',
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Volt::test('admin.profile.info-form')
        ->set('nombre', 'Nuevo')
        ->set('apellido_paterno', 'Apellido')
        ->set('email', $admin->email)
        ->call('guardar')
        ->assertHasNoErrors();

    $admin->refresh();
    expect($admin->nombre)->toBe('Nuevo');
    expect($admin->apellido_paterno)->toBe('Apellido');
});

it('no elimina la cuenta sin confirmacion y password', function () {
    $admin = User::factory()->create([
        'email' => 'admin.del.'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');

    // Segundo admin para no bloquear por “último admin”.
    $otro = User::factory()->create(['email' => 'admin2.'.uniqid().'@example.com']);
    $otro->assignRole('admin');

    $this->actingAs($admin);

    Volt::test('admin.profile.delete-form')
        ->set('acepto_riesgo', true)
        ->set('confirmacion', 'BORRAR')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors('confirmacion');

    expect($admin->fresh())->not->toBeNull();
});

it('no elimina la cuenta con password incorrecta', function () {
    $admin = User::factory()->create([
        'email' => 'admin.pwd.'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');
    $otro = User::factory()->create(['email' => 'admin2b.'.uniqid().'@example.com']);
    $otro->assignRole('admin');

    $this->actingAs($admin);

    Volt::test('admin.profile.delete-form')
        ->set('acepto_riesgo', true)
        ->set('confirmacion', 'ELIMINAR')
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect($admin->fresh())->not->toBeNull();
});

it('bloquea borrar el unico administrador', function () {
    $admin = User::factory()->create([
        'email' => 'admin.solo.'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $component = Volt::test('admin.profile.delete-form')
        ->set('acepto_riesgo', true)
        ->set('confirmacion', 'ELIMINAR')
        ->set('password', 'password')
        ->call('deleteUser');

    expect($admin->fresh())->not->toBeNull();
    expect($component->get('errorGeneral'))->not->toBeNull();
});
