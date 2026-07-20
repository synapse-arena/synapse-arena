<?php 
namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class CohereService implements AiAgentInterface
{
    public function generateArgument(string $topic, string $stance, array $history): string
    {
        $apiKey = env('COHERE_API_KEY');
        $url = "https://api.cohere.com/v1/chat";

        $preamble = "Kamu adalah pengamat politik dan juri debat. Kamu ditugaskan mengambil posisi {$stance} untuk mosi: '{$topic}'. Buat argumen analitis yang menyerang celah dari argumen lawan sebelumnya. Jawab dalam 3 kalimat saja.";
        $userPrompt = "Konteks riwayat perdebatan: " . json_encode($history) . "\n\nSilakan sampaikan argumenmu sekarang.";

        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'command-r', 
            'preamble' => $preamble,
            'message' => $userPrompt,
            'temperature' => 0.7
        ]);

        return $response->json('text') ?? 'Cohere gagal merangkai argumen.';
    }
}