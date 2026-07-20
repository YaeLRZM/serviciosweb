<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImagenArticuloRequest;
use App\Http\Requests\UpdateImagenArticuloRequest;
use App\Models\ImagenArticulo;
use Illuminate\Support\Facades\DB;

class ImagenArticuloController extends Controller
{
    public function index()
    {
        return ImagenArticulo::all();
    }

    public function create()
    {
        //
    }

    /**
     * Crea (o reemplaza como principal) una imagen por URL del artículo.
     * Alcance mínimo: una imagen principal por producto.
     */
    public function store(StoreImagenArticuloRequest $request)
    {
        $data = $request->validated();
        $esPrincipal = array_key_exists('es_principal', $data)
            ? (bool) $data['es_principal']
            : true;

        $imagen = DB::transaction(function () use ($data, $esPrincipal) {
            $articuloId = (int) $data['articulo_id'];

            if ($esPrincipal) {
                // Una sola principal: desmarca las demás.
                ImagenArticulo::query()
                    ->where('articulo_id', $articuloId)
                    ->update(['es_principal' => false]);
            }

            // Si ya hay una imagen principal y pedimos principal, actualizamos
            // la primera principal/histórica para no acumular filas basura.
            if ($esPrincipal) {
                $existing = ImagenArticulo::query()
                    ->where('articulo_id', $articuloId)
                    ->orderByDesc('es_principal')
                    ->orderBy('id')
                    ->first();

                if ($existing) {
                    $existing->update([
                        'url' => $data['url'],
                        'es_principal' => true,
                    ]);

                    return $existing->fresh();
                }
            }

            return ImagenArticulo::create([
                'articulo_id' => $articuloId,
                'url' => $data['url'],
                'es_principal' => $esPrincipal,
            ]);
        });

        return response()->json([
            'message' => 'Imagen de artículo guardada correctamente',
            'imagenArticulo' => $imagen,
        ], 201);
    }

    public function show(ImagenArticulo $imagenArticulo)
    {
        return response()->json(['imagenArticulo' => $imagenArticulo], 200);
    }

    public function edit(ImagenArticulo $imagenArticulo)
    {
        //
    }

    public function update(UpdateImagenArticuloRequest $request, ImagenArticulo $imagenArticulo)
    {
        $imagenArticulo->update($request->validated());

        return response()->json([
            'message' => 'Imagen de artículo actualizada correctamente',
            'imagenArticulo' => $imagenArticulo,
        ], 200);
    }

    public function destroy(ImagenArticulo $imagenArticulo)
    {
        $imagenArticulo->delete();

        return response()->json(['message' => 'Imagen de artículo eliminada correctamente'], 200);
    }
}
