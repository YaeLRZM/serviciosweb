<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

$responderSesionExpirada = static function (Request $request, string $origen) {
    Log::warning('Sesión o token CSRF vencido (419)', [
        'origen' => $origen,
        'path' => $request->path(),
        'method' => $request->method(),
        'user_id' => optional($request->user())->id,
        'ip' => $request->ip(),
        'livewire' => $request->hasHeader('X-Livewire'),
        'wants_json' => $request->expectsJson() || $request->ajax(),
    ]);

    $mensaje = 'Tu sesión expiró por seguridad. Vuelve a iniciar sesión para continuar.';

    // Livewire / AJAX: JSON 419 (el front evita el confirm() en inglés).
    if (
        $request->hasHeader('X-Livewire')
        || $request->expectsJson()
        || $request->ajax()
        || $request->wantsJson()
    ) {
        return response()->json([
            'message' => $mensaje,
            'mensaje' => $mensaje,
            'redirect' => route('login', ['expired' => 1]),
        ], 419);
    }

    // Formularios POST clásicos: redirigir al acceso con aviso claro.
    return redirect()
        ->guest(route('login', ['expired' => 1]))
        ->with('status', $mensaje);
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['chatbot']);

        // No redirigir guests de la API a la ruta web /login.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return route('login');
        });

        // Si ya hay sesión y se visita /login (guest), no rebotar a /:
        // mandar admins al panel; el resto a la landing.
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return '/admin/dashboard';
            }

            return '/';
        });

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($responderSesionExpirada): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
                ], 401);
            }
        });

        /**
         * 419 Page Expired (CSRF / sesión vencida).
         * Laravel puede convertir TokenMismatchException en HttpException(419).
         */
        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($responderSesionExpirada) {
            return $responderSesionExpirada($request, 'TokenMismatchException');
        });

        $exceptions->render(function (HttpException $e, Request $request) use ($responderSesionExpirada) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            return $responderSesionExpirada($request, 'HttpException419');
        });
    })->create();
