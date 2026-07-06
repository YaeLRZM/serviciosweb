<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\JsonStorageService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    private $storage;

    public function __construct()
    {
        $this->storage = new JsonStorageService();
    }

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Obtener lista de artículos",
     *     tags={"Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de artículos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nombre", type="string"),
     *                 @OA\Property(property="precio", type="number", format="float")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $articles = $this->storage->read('articles.json');

        return response()->json($articles);
    }

    /**
     * @OA\Post(
     *     path="/api/articles",
     *     summary="Crear nuevo artículo",
     *     tags={"Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","precio"},
     *             @OA\Property(property="nombre", type="string", example="Laptop"),
     *             @OA\Property(property="precio", type="number", format="float", example=999.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artículo creado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="article", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validación fallida")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        $articles = $this->storage->read('articles.json');

        $article = [
            'id' => $this->storage->getNextId('articles.json'),
            'nombre' => $validated['nombre'],
            'precio' => (float)$validated['precio'],
        ];

        $articles[] = $article;
        $this->storage->write('articles.json', $articles);

        return response()->json([
            'message' => 'Artículo creado exitosamente',
            'article' => $article,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Obtener artículo por ID",
     *     tags={"Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del artículo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del artículo",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="precio", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Artículo no encontrado")
     * )
     */
    public function show($id)
    {
        $articles = $this->storage->read('articles.json');
        $article = collect($articles)->firstWhere('id', (int)$id);

        if (!$article) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }

        return response()->json($article);
    }

    /**
     * @OA\Put(
     *     path="/api/articles/{id}",
     *     summary="Actualizar artículo",
     *     tags={"Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del artículo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="precio", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo actualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="article", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Artículo no encontrado")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'string|max:255',
            'precio' => 'numeric|min:0',
        ]);

        $articles = $this->storage->read('articles.json');
        $key = collect($articles)->search(fn($a) => $a['id'] == $id);

        if ($key === false) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }

        if (isset($validated['nombre'])) {
            $articles[$key]['nombre'] = $validated['nombre'];
        }

        if (isset($validated['precio'])) {
            $articles[$key]['precio'] = (float)$validated['precio'];
        }

        $this->storage->write('articles.json', $articles);

        return response()->json([
            'message' => 'Artículo actualizado',
            'article' => $articles[$key],
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/articles/{id}",
     *     summary="Eliminar artículo",
     *     tags={"Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del artículo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Artículo no encontrado")
     * )
     */
    public function destroy($id)
    {
        $articles = $this->storage->read('articles.json');
        $key = collect($articles)->search(fn($a) => $a['id'] == $id);

        if ($key === false) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }

        unset($articles[$key]);
        $this->storage->write('articles.json', array_values($articles));

        return response()->json(['message' => 'Artículo eliminado'], 200);
    }
}
