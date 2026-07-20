<?php

namespace App\Services\Ai;

interface AiAgentInterface
{
    /**
     * Fungsi wajib untuk meminta respons dari AI.
     * * @param string $topic Mosi debat
     * @param string $stance Sikap AI ('pro' atau 'contra')
     * @param array $history Riwayat argumen sebelumnya agar AI paham konteks
     * @return string Hasil teks argumen dari AI
     */
    public function generateArgument(string $topic, string $stance, array $history): string;
}