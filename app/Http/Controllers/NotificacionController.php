<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Services\VentaAutoCompleteService;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request, VentaAutoCompleteService $autoComplete)
    {
        // Al abrir notificaciones, aplicar vencimientos de compras.
        $autoComplete->completarVencidas();

        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $items = Notificacion::query()
            ->where('user_id', (int) $user->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $noLeidas = $items->whereNull('leida_at')->count();

        return response()->json([
            'data' => $items,
            'meta' => [
                'count' => $items->count(),
                'no_leidas' => $noLeidas,
            ],
        ]);
    }

    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        $user = $request->user('api');
        if (! $user || (int) $notificacion->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ($notificacion->leida_at === null) {
            $notificacion->leida_at = now();
            $notificacion->save();
        }

        return response()->json(['notificacion' => $notificacion]);
    }

    public function marcarTodasLeidas(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        Notificacion::query()
            ->where('user_id', (int) $user->id)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return response()->json(['message' => 'Notificaciones marcadas como leídas']);
    }
}
