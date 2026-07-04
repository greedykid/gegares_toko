<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        // Read from config (not env() directly) so values survive config caching.
        $this->apiKey = config('services.ai.key');
        $this->baseUrl = config('services.ai.base_url', 'https://lite.koboillm.com/v1');
        $this->model = config('services.ai.model', 'gemini-3-flash-preview');
    }

    /**
     * Send a multi-turn chat message to the AI with full conversation history.
     *
     * @param string $message The current user message
     * @param string $systemContext System instructions and context data
     * @param array  $conversationHistory Previous messages in [['role' => 'user'|'assistant', 'content' => '...'], ...] format
     * @param float  $temperature Creativity level (0.0 = deterministic, 1.0 = creative)
     * @param int    $maxTokens Maximum response length
     */
    public function chat(
        string $message,
        string $systemContext = '',
        array $conversationHistory = [],
        float $temperature = 0.7,
        int $maxTokens = 1024
    ): ?string {
        if (empty($this->apiKey)) {
            Log::error('AI Chat Error: AI_API_KEY is not configured (check .env and run config:cache).');
            return 'Maaf, layanan asisten AI sedang tidak tersedia untuk sementara. Silakan coba lagi nanti ya, Kak!';
        }

        try {
            $messages = [];

            // 1. System instructions as the opening user/assistant pair
            if ($systemContext) {
                $messages[] = ['role' => 'user', 'content' => "SYSTEM INSTRUCTIONS: " . $systemContext];
                $messages[] = ['role' => 'assistant', 'content' => "Understood. I will strictly follow those instructions and only use the provided context data."];
            }

            // 2. Inject conversation history for multi-turn memory
            foreach ($conversationHistory as $turn) {
                $role = $turn['role'] === 'assistant' ? 'assistant' : 'user';
                $content = $turn['content'] ?? '';
                if (!empty($content)) {
                    $messages[] = ['role' => $role, 'content' => $content];
                }
            }

            // 3. Current user message
            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('AI Chat Error: ' . $response->body());

                // Track moderation block if applicable
                if (stripos($response->body(), 'safety') !== false || $response->status() === 400) {
                    return 'MODERATION_BLOCK';
                }

                return 'Maaf, saya sedang mengalami kendala teknis. Coba sebentar lagi ya!';
            }

            return $response->json('choices.0.message.content');
        } catch (\Exception $e) {
            Log::error('AI Exception: ' . $e->getMessage());
            return 'Oops, terjadi kesalahan sistem.';
        }
    }

    /**
     * Analyze an image to identify products (Snap & Buy).
     */
    public function analyzeImage(string $base64Image, string $prompt = "Identifikasi makanan di gambar ini. Apakah ini salah satu dari jajanan tradisional Indonesia? Jika ya, sebutkan namanya saja."): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('AI Image Analysis Error: AI_API_KEY is not configured (check .env and run config:cache).');
            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:image/jpeg;base64,{$base64Image}"
                                    ]
                                ]
                            ]
                        ],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 800,
                ]);

            if ($response->failed()) {
                Log::error('AI Image Analysis Error: ' . $response->body());

                if (stripos($response->body(), 'safety') !== false || $response->status() === 400) {
                    return 'MODERATION_BLOCK';
                }

                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (\Exception $e) {
            Log::error('AI Image Analysis Exception: ' . $e->getMessage());
            return null;
        }
    }
}
