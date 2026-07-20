<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cliente mínimo de Gemini (solo servidor).
 * Devuelve texto o null para que el caller use el bot de reglas.
 */
class GeminiChatService
{
    private const DEFAULT_MODEL = 'gemini-2.0-flash';

    private const SYSTEM_PROMPT = <<<'TXT'
Eres el asistente de la tienda Ixé Moda (artesanías mexicanas: textiles, huipiles, barro, alebrijes, etc.).
Responde en español, de forma breve y amable (máximo ~120 palabras).
Ayuda a buscar o describir productos por color, región, material o tipo de artesanía.
No inventes precios exactos ni stock. Si no sabes algo, dilo con honestidad.
TXT;

    public function isConfigured(): bool
    {
        $key = config('services.gemini.api_key');

        return is_string($key) && trim($key) !== '';
    }

    /**
     * Intenta una respuesta con Gemini.
     *
     * @return string|null Texto de respuesta, o null si no hay key / error / vacío.
     */
    public function reply(string $userMessage): ?string
    {
        $userMessage = trim($userMessage);
        if ($userMessage === '' || ! $this->isConfigured()) {
            return null;
        }

        $apiKey = trim((string) config('services.gemini.api_key'));
        $baseUrl = rtrim((string) config('services.gemini.base_url'), '/');
        $model = (string) (config('services.gemini.model') ?: self::DEFAULT_MODEL);

        $url = "{$baseUrl}/models/{$model}:generateContent";

        try {
            $response = Http::timeout(12)
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => self::SYSTEM_PROMPT],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userMessage],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.6,
                        'maxOutputTokens' => 512,
                    ],
                ]);

            // 429: cuota / rate limit — fallback silencioso al bot de reglas.
            if ($response->status() === 429) {
                Log::warning('Gemini rate limit reached', [
                    'status' => 429,
                    'retry_after' => $response->header('Retry-After'),
                ]);

                return null;
            }

            if (! $response->successful()) {
                Log::warning('Gemini chat HTTP error', [
                    'status' => $response->status(),
                    // No loguear body completo (puede filtrar detalles); sin API key.
                    'body_snippet' => mb_substr($response->body(), 0, 200),
                ]);

                return null;
            }

            $data = $response->json();
            $text = data_get($data, 'candidates.0.content.parts.0.text');

            if (! is_string($text) || trim($text) === '') {
                Log::warning('Gemini chat empty candidates', [
                    'has_candidates' => isset($data['candidates']),
                ]);

                return null;
            }

            return trim($text);
        } catch (Throwable $e) {
            Log::warning('Gemini chat exception', [
                'error' => $e->getMessage(),
                'type' => $e::class,
            ]);

            return null;
        }
    }
}
