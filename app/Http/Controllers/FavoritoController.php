<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoritoController extends Controller
{
    /**
     * Listado de favoritos del usuario autenticado (artículos reales).
     * GET /api/favoritos
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('vendedor')) {
            return response()->json([
                'message' => 'Acción no permitida para cuentas vendedor',
            ], 403);
        }

        $favoritos = Favorito::query()
            ->where('user_id', (int) $user->id)
            ->with([
                'articulo.imagenes',
                'articulo.categoria:id,nombre',
                'articulo.tienda:id,nombre',
                'articulo.artesano:id,nombre',
            ])
            ->orderByDesc('id')
            ->get();

        $articulos = $favoritos
            ->map(fn (Favorito $f) => $f->articulo)
            ->filter()
            ->values();

        $ids = $favoritos->pluck('articulo_id')->map(fn ($id) => (int) $id)->values();

        return response()->json([
            'data' => $articulos,
            'meta' => [
                'count' => $ids->count(),
                'articulo_ids' => $ids,
            ],
        ]);
    }

    /**
     * Marca un artículo como favorito del usuario autenticado.
     * POST /api/favoritos  body: { articulo_id }
     */
    public function store(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('vendedor')) {
            return response()->json([
                'message' => 'Acción no permitida para cuentas vendedor',
            ], 403);
        }

        $data = $request->validate([
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
        ]);

        $articuloId = (int) $data['articulo_id'];

        // Idempotente: no duplicar (unique en BD).
        $favorito = Favorito::query()->firstOrCreate([
            'user_id' => (int) $user->id,
            'articulo_id' => $articuloId,
        ]);

        $favorito->load([
            'articulo.imagenes',
            'articulo.categoria:id,nombre',
            'articulo.tienda:id,nombre',
            'articulo.artesano:id,nombre',
        ]);

        return response()->json([
            'message' => 'Artículo guardado en favoritos',
            'favorito' => $favorito,
            'articulo_id' => $articuloId,
        ], $favorito->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Quita un favorito del usuario autenticado.
     * DELETE /api/favoritos/{articuloId}
     */
    public function destroy(Request $request, int $articuloId)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('vendedor')) {
            return response()->json([
                'message' => 'Acción no permitida para cuentas vendedor',
            ], 403);
        }

        if ($articuloId <= 0) {
            throw ValidationException::withMessages([
                'articulo_id' => ['Artículo inválido.'],
            ]);
        }

        // Solo del usuario autenticado (no borra favoritos ajenos).
        Favorito::query()
            ->where('user_id', (int) $user->id)
            ->where('articulo_id', $articuloId)
            ->delete();

        return response()->json([
            'message' => 'Artículo quitado de favoritos',
            'articulo_id' => $articuloId,
        ]);
    }
}
