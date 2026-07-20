<?php
namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GroqService implements AiAgentInterface
{
    public function generateArgument(string $topic, string $stance, array $history): string
    {
        $apiKey = env('GROQ_API_KEY');
        $url = "https://api.groq.com/openai/v1/chat/completions";

        $systemInstruction = "Kamu adalah pakar debat profesional di posisi {$stance} untuk mosi: '{$topic}'. Berikan sanggahan yang agresif namun tetap sopan dan berbasis data. Maksimal 3 kalimat.";

        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'llama3-8b-8192',
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => "Berikut riwayat debat sebelumnya: " . json_encode($history)]
            ]
        ]);

        return $response->json('choices.0.message.content') ?? 'Groq gagal merespons.';
    }
}