<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Services\GeminiChatService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ChatbotController extends Controller
{
    #[OA\Post(
        path: '/chatbot',
        summary: 'Asistente conversacional de artículos (público)',
        description: 'Recibe un mensaje del usuario. Intenta Gemini si hay API key; '
            . 'si no, o si Gemini falla, usa BotMan + catálogo (reglas).',
        tags: ['Chatbot'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['driver', 'message'],
                properties: [
                    new OA\Property(property: 'driver', type: 'string', example: 'web'),
                    new OA\Property(property: 'message', type: 'string', example: 'busco algo rojo de oaxaca'),
                    new OA\Property(property: 'userId', type: 'string', example: '1'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta(s) del asistente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(
                            property: 'messages',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'type', type: 'string', example: 'text'),
                                    new OA\Property(property: 'text', type: 'string', example: 'Huipil bordado — rojo, Oaxaca (Artesanías del Sur)'),
                                    new OA\Property(property: 'attachment', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'additionalParameters', type: 'array', items: new OA\Items(type: 'string')),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function handle(Request $request, GeminiChatService $gemini)
    {
        $userText = $this->extractUserText($request);

        // 1) Gemini (si hay key y responde). Formato compatible con Flutter/BotMan web.
        if ($userText !== '') {
            $geminiText = $gemini->reply($userText);
            if (is_string($geminiText) && $geminiText !== '') {
                return response()->json($this->botmanShape([$geminiText]));
            }
        }

        // 2) Fallback: bot de reglas actual (BotMan + Eloquent).
        $botman = app('botman');

        $botman->hears('hola', function ($bot) {
            $bot->reply('¡Hola! Soy el asistente de artículos. Dime qué buscas: color, región, tipo de bordado o tela.');
        });

        $botman->fallback(function ($bot) {
            $texto = mb_strtolower($bot->getMessage()->getText());

            $articulos = Articulo::with(['categoria', 'artesano', 'tienda', 'imagenes'])
                ->when($this->buscar($texto, ['rojo', 'azul', 'verde', 'negro', 'blanco']),
                    fn ($q, $v) => $q->whereRaw('LOWER(color) = ?', [$v]))
                ->when($this->buscar($texto, ['oaxaca', 'chiapas', 'puebla', 'yucatán']),
                    fn ($q, $v) => $q->whereRaw('LOWER(region) = ?', [$v]))
                ->when(str_contains($texto, 'punto de cruz'),
                    fn ($q) => $q->whereRaw('LOWER(bordado) = ?', ['punto de cruz']))
                ->limit(5)
                ->get();

            if ($articulos->isEmpty()) {
                $bot->reply('No encontré artículos con esa descripción. Prueba con un color, región o tipo de bordado.');

                return;
            }

            foreach ($articulos as $a) {
                $bot->reply("{$a->nombre} — {$a->color}, {$a->region} ({$a->tienda?->nombre})");
            }
        });

        $botman->listen();
    }

    /**
     * Extrae texto del payload BotMan web (message string o {text: ...}).
     */
    private function extractUserText(Request $request): string
    {
        $message = $request->input('message');

        if (is_array($message)) {
            return trim((string) ($message['text'] ?? ''));
        }

        return trim((string) ($message ?? ''));
    }

    /**
     * Shape JSON que ya consume Flutter (ChatbotService).
     *
     * @param  list<string>  $texts
     * @return array{status: int, messages: list<array{type: string, text: string, attachment: null, additionalParameters: array}>}
     */
    private function botmanShape(array $texts): array
    {
        $messages = [];
        foreach ($texts as $text) {
            $messages[] = [
                'type' => 'text',
                'text' => $text,
                'attachment' => null,
                'additionalParameters' => [],
            ];
        }

        return [
            'status' => 200,
            'messages' => $messages,
        ];
    }

    private function buscar(string $texto, array $opciones): ?string
    {
        foreach ($opciones as $o) {
            if (str_contains($texto, $o)) {
                return $o;
            }
        }

        return null;
    }
}
