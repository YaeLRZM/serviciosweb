<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResenaRequest;
use App\Http\Requests\UpdateResenaRequest;
use App\Models\Resena;
use App\Services\NotificacionService;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    /**
     * Listado público. Filtro opcional: ?articulo_id=
     */
    public function index(Request $request)
    {
        $query = Resena::query()->with(['user:id,nombre,email'])->latest();

        if ($request->filled('articulo_id')) {
            $query->where('articulo_id', (int) $request->input('articulo_id'));
        }

        return $query->get();
    }

    /**
     * Opiniones del usuario autenticado (solo las suyas).
     */
    public function mias(Request $request)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $items = Resena::query()
            ->where('user_id', (int) $user->id)
            ->with(['articulo:id,nombre'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $items,
            'meta' => ['count' => $items->count()],
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Crear reseña (JWT + permiso crearResenas).
     * user_id se toma del token; no se acepta del body.
     */
    public function store(StoreResenaRequest $request, NotificacionService $notificaciones)
    {
        $data = $request->validated();
        $data['user_id'] = auth('api')->id();
        // Columna comentario es NOT NULL en migración.
        $data['comentario'] = $data['comentario'] ?? '';

        $resena = Resena::create($data);
        $resena->load(['user:id,nombre,email', 'articulo:id,nombre,tienda_id']);

        $notificaciones->nuevaResena($resena);

        return response()->json([
            'message' => 'Reseña creada correctamente',
            'resena' => $resena,
        ], 201);
    }

    public function show(Resena $resena)
    {
        $resena->load(['user:id,nombre,email']);

        return response()->json(['resena' => $resena], 200);
    }

    public function edit(Resena $resena)
    {
        //
    }

    public function update(UpdateResenaRequest $request, Resena $resena)
    {
        // Ownership del autor en UpdateResenaRequest.
        $data = $request->validated();
        // No permitir cambiar autor ni artículo.
        unset($data['user_id'], $data['articulo_id']);

        if (array_key_exists('comentario', $data) && $data['comentario'] === null) {
            $data['comentario'] = '';
        }

        $resena->update($data);
        $resena->load(['user:id,nombre,email', 'articulo:id,nombre']);

        return response()->json([
            'message' => 'Opinión actualizada correctamente',
            'resena' => $resena,
        ], 200);
    }

    public function destroy(Request $request, Resena $resena)
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isOwner = (int) $resena->user_id === (int) $user->id;
        $isAdmin = $user->hasRole('admin');

        // Solo el dueño o un admin pueden borrar.
        if (! $isOwner && ! $isAdmin) {
            return response()->json([
                'message' => 'No puedes eliminar esta opinión.',
            ], 403);
        }

        $resena->delete();

        return response()->json([
            'message' => 'Opinión eliminada correctamente',
        ], 200);
    }
}
