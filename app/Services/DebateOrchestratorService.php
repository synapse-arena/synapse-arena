<?php
namespace App\Services;

use App\Models\DebateRoom;
use App\Jobs\GenerateAiArgument;

class DebateOrchestratorService
{
    public function triggerNextTurn(DebateRoom $room)
    {
        // Jika ruangan sudah dihentikan/selesai, hentikan proses
        if ($room->status !== 'live') {
            return;
        }

        $participants = $room->participants()->get();
        if ($participants->isEmpty()) return;

        // Hitung sudah berapa argumen yang ada di ruangan ini
        $currentTurnCount = $room->arguments()->count();
        $totalParticipants = $participants->count();
        
        // Misal: Max 3 ronde. 2 Partisipan. Total argumen maksimal = 6.
        $maxTotalTurns = $room->max_rounds * $totalParticipants;

        if ($currentTurnCount >= $maxTotalTurns) {
            // Jika debat sudah mencapai batas ronde, ubah status jadi archived
            $room->update(['status' => 'archived']);
            return;
        }

        // Tentukan giliran siapa sekarang (Logika Round-Robin)
        // Jika argumen ke-0 -> indeks 0. Jika argumen ke-1 -> indeks 1.
        $nextParticipantIndex = $currentTurnCount % $totalParticipants;
        $nextParticipant = $participants[$nextParticipantIndex];
        
        $nextTurnOrder = $currentTurnCount + 1;

        // Lempar tugas menembak API AI ke Background Job!
        GenerateAiArgument::dispatch($room, $nextParticipant, $nextTurnOrder);
    }
}