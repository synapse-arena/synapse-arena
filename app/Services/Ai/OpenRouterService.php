<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class OpenRouterService implements AiAgentInterface
{
    public function generateArgument(string $topic, string $stance, array $history): string
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $url = "https://openrouter.ai/api/v1/chat/completions";

        $systemInstruction = "Kamu adalah peserta debat tingkat nasional yang cerdas. Kamu berada di pihak {$stance} pada mosi: '{$topic}'. Jangan bertele-tele, gunakan fakta teknis untuk membungkam lawan. Maksimal 3 kalimat.";
        $userPrompt = "Riwayat argumen sebelumnya: " . json_encode($history) . "\n\nGiliranmu membalas.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'HTTP-Referer' => env('APP_URL'), // Syarat dari OpenRouter
            'X-Title' => 'Synapse Arena', // Syarat dari OpenRouter
        ])->post($url, [
            'model' => 'google/gemma-2-9b-it:free', // Model gratis di OpenRouter
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userPrompt]
            ]
        ]);

        return $response->json('choices.0.message.content') ?? 'Model OpenRouter sedang sibuk.';
    }
}