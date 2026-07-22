<?php

use App\Models\User;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');
});

it('permite keep-alive solo a usuarios autenticados', function () {
    $this->get(route('session.keep-alive'))
        ->assertRedirect();

    $admin = User::factory()->create([
        'email' => 'admin.keep.'.uniqid().'@example.com',
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->getJson(route('session.keep-alive'))
        ->assertOk()
        ->assertJsonStructure(['ok', 'csrf', 'server_time'])
        ->assertJson(['ok' => true]);
});

it('maneja token CSRF vencido sin página genérica en inglés', function () {
    $admin = User::factory()->create([
        'email' => 'admin.csrf.'.uniqid().'@example.com',
    ]);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    // Simular render del handler de TokenMismatchException.
    $request = request();
    $request->headers->set('X-Livewire', 'true');
    $request->headers->set('Accept', 'application/json');

    $handler = null;
    try {
        throw new TokenMismatchException('CSRF token mismatch.');
    } catch (TokenMismatchException $e) {
        // Disparar el sistema de excepciones de Laravel.
        $response = app('Illuminate\Contracts\Debug\ExceptionHandler')->render($request, $e);
    }

    expect($response->getStatusCode())->toBe(419);
    $json = $response->getData(true);
    expect($json['mensaje'] ?? '')->toContain('sesión');
    expect($json['redirect'] ?? '')->toContain('login');
});

it('usa una duración de sesión razonable para el panel', function () {
    expect((int) config('session.lifetime'))->toBeGreaterThanOrEqual(120);
});
