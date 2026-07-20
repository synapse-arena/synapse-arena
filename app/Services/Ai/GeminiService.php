<?php
namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GeminiService implements AiAgentInterface
{
    public function generateArgument(string $topic, string $stance, array $history): string
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        // Rakit instruksi karakter (System Prompt)
        $systemInstruction = "Kamu adalah pakar debat profesional. Kamu berada dalam posisi yang {$stance} (setuju/tidak setuju) terhadap mosi: '{$topic}'. Berikan argumen yang sangat kuat, tajam, dan akademis. Batasi respons maksimal 3 kalimat singkat.";

        // Kirim permintaan via HTTP Facade Laravel
        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $systemInstruction . "\n\nBerikut riwayat debat sebelumnya: " . json_encode($history)]]]
            ]
        ]);

        return $response->json('candidates.0.content.parts.0.text') ?? 'Gemini gagal merespons.';
    }
}