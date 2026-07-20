<?php

namespace App\Jobs;

use App\Models\DebateRoom;
use App\Models\Participant;
use App\Models\Argument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Import semua Factory AI kita
use App\Services\Ai\GeminiService;
use App\Services\Ai\GroqService;
use App\Services\Ai\MistralService;
use App\Services\Ai\CohereService;
use App\Services\Ai\OpenRouterService;
use App\Services\DebateOrchestratorService;

class GenerateAiArgument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $room;
    public $participant;
    public $turnOrder;

    // Timeout: Jika API AI ngadat lebih dari 60 detik, batalkan job ini
    public $timeout = 60;

    public function __construct(DebateRoom $room, Participant $participant, int $turnOrder)
    {
        $this->room = $room;
        $this->participant = $participant;
        $this->turnOrder = $turnOrder;
    }

    public function handle(DebateOrchestratorService $orchestrator): void
    {
        // 1. Ambil riwayat debat sebelumnya sebagai konteks untuk AI
        $historyData = Argument::where('debate_room_id', $this->room->id)
                        ->orderBy('turn_order', 'asc')
                        ->get(['content'])
                        ->pluck('content')
                        ->toArray();

        // 2. Pilih Agen AI (Strategy Pattern)
        $aiAgent = match($this->participant->ai_model_name) {
            'Gemini 1.5 Pro' => new GeminiService(),
            'Llama 3 (Groq)' => new GroqService(),
            'Mistral 7B'     => new MistralService(),
            'Command-R'      => new CohereService(),
            'Gemma 2'        => new OpenRouterService(),
            default          => new GeminiService(),
        };

        // 3. Tembak API! (Ini berjalan di background)
        $argumentText = $aiAgent->generateArgument($this->room->topic, $this->participant->stance, $historyData);

        // 4. Simpan hasilnya ke database
        Argument::create([
            'debate_room_id' => $this->room->id,
            'participant_id' => $this->participant->id,
            'content'        => $argumentText,
            'turn_order'     => $this->turnOrder,
        ]);

        // 5. Panggil kembali Orchestrator untuk mengecek giliran siapa selanjutnya
        $orchestrator->triggerNextTurn($this->room);
    }
}
