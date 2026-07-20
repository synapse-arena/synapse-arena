<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class MistralService implements AiAgentInterface
{
    public function generateArgument(string $topic, string $stance, array $history): string
    {
        $apiKey = env('MISTRAL_API_KEY');
        $url = "https://api.mistral.ai/v1/chat/completions";

        $systemInstruction = "Kamu adalah pendebat ulung. Posisi kamu adalah {$stance} terkait mosi: '{$topic}'. Berikan argumen logis, filosofis, dan langsung pada intinya. Batasi respons maksimal 3 kalimat padat.";
        $userPrompt = "Berikut riwayat debat sejauh ini: " . json_encode($history) . "\n\nSekarang, berikan giliran argumenmu.";

        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'open-mistral-7b', // Model gratis dan cepat dari Mistral
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'max_tokens' => 150
        ]);

        return $response->json('choices.0.message.content') ?? 'Mistral mengalami gangguan sinyal.';
    }
}