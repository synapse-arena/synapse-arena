<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\DebateRoom;
use App\Models\Argument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessAiDebate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $roomId;
    public $turnOrder;
    public $stance;
    public $timeout = 120; 

    public function __construct($roomId, $turnOrder = 1, $stance = 'pro')
    {
        $this->roomId = $roomId;
        $this->turnOrder = $turnOrder;
        $this->stance = $stance;
    }

    public function handle(): void
    {
        $room = DebateRoom::find($this->roomId);
        if (!$room) return;

        $history = Argument::where('debate_room_id', $this->roomId)->orderBy('created_at', 'asc')->get();
            
        $topicLabel = ($room->mode === 'discussion') ? "Topik Diskusi" : "Mosi Debat";
        $contextString = $topicLabel . ": " . $room->topic . "\n\n";
        
        $lastArgument = null;
        foreach ($history as $arg) {
            // Jangan sertakan kesimpulan ke dalam konteks Prompt
            if ($arg->stance !== 'kesimpulan') {
                $contextString .= strtoupper($arg->stance) . ": " . $arg->content . "\n";
                // Simpan argumen terakhir untuk referensi respon AI
                if ($arg->stance !== 'prompter') {
                    $lastArgument = $arg->content;
                }
            }
        }

        $aiResponse = "";
        $aiName = "";

        // ==========================================
        // TENTUKAN SYSTEM PROMPT BERDASARKAN MODE
        // ==========================================
        if ($this->stance === 'kesimpulan') {
            $systemPrompt = "Anda adalah moderator. Baca percakapan di atas, lalu berikan HANYA 1 kalimat kesimpulan pamungkas (sangat singkat, padat) yang merangkum hasil akhir pemikiran mereka.";
            $fullPrompt = $contextString . "\nBerikan 1 kalimat kesimpulan Anda.";
        } 
        elseif ($this->stance === 'ai_answer') { // JIKA INI JAWABAN UNTUK PERTANYAAN PROMPTER
            $systemPrompt = "Anda mewakili seluruh pakar AI dalam forum ini. Prompter/Sutradara baru saja mengajukan pertanyaan atau tanggapan lanjutan setelah sesi selesai.\n";
            $systemPrompt .= "Jawablah dengan komprehensif, bijak, dan berdasarkan konteks perdebatan/diskusi yang sudah terjadi di atas.\n";
            $systemPrompt .= "Panjang maksimal 2 paragraf. Gunakan tanda bintang ganda (**kata penting**) untuk highlight.";
            $fullPrompt = $contextString . "\nSilakan jawab pertanyaan Prompter tersebut secara langsung.";
        }
        else if ($room->mode === 'discussion') {
            $systemPrompt = "Anda adalah Pakar Ahli ke-" . $this->turnOrder . " dalam diskusi.\n";
            $systemPrompt .= "Tugas Anda: Lanjutkan, perluas, atau berikan sudut pandang baru yang MENDUKUNG ide pemikir sebelumnya.\n";
            
            // PERINTAH AGAR AI NYAMBUNG SATU SAMA LAIN
            if ($lastArgument && $this->turnOrder > 1) {
                $systemPrompt .= "WAJIB KONEKSI: Awali kalimat Anda dengan secara spesifik mengutip/merujuk pada ide spesifik dari pakar sebelumnya, lalu kembangkan ide tersebut!\n";
            }

            $systemPrompt .= "JANGAN MEMBANTAH/MENENTANG. Bertindaklah seperti rekan kerja yang sedang brainstorming kooperatif.\n";
            $systemPrompt .= "Panjang maksimal 2 paragraf. Gunakan tanda bintang ganda (**kata penting**) untuk highlight.\n";
            $systemPrompt .= "Jangan gunakan sapaan basa-basi (seperti 'Saya setuju dengan rekan saya'). Langsung ke inti pemikiran Anda.";
            $fullPrompt = $contextString . "\nSekarang giliran Anda (Pemikir " . $this->turnOrder . ").";
        }
        else {
            $systemPrompt = "Anda adalah debater di pihak " . strtoupper($this->stance) . ".\n";
            
            // PERINTAH AGAR AI NYAMBUNG SATU SAMA LAIN (DEBAT)
            if ($lastArgument && $this->turnOrder > 1) {
                $systemPrompt .= "WAJIB KONEKSI: Awali kalimat Anda dengan menyerang atau membantah secara langsung poin spesifik yang baru saja disampaikan oleh lawan Anda sebelumnya!\n";
            }

            $systemPrompt .= "Berikan argumen balasan maksimal 2 paragraf yang logis dan serang logika lawan.\n";
            $systemPrompt .= "Gunakan tanda bintang ganda (**kata penting**) untuk menyorot poin utama.\n";
            $systemPrompt .= "Jangan gunakan salam pembuka.";
            $fullPrompt = $contextString . "\nSekarang giliran Anda (" . strtoupper($this->stance) . ").";
        }

        // ==========================================
        // PANGGIL API AI
        // ==========================================
        if ($this->stance === 'kesimpulan' || $this->stance === 'ai_answer') {
            $aiName = ($this->stance === 'ai_answer') ? "Panelis AI Gabungan" : "Moderator (Gemini)";
            $aiResponse = $this->callGeminiApi($fullPrompt, $systemPrompt);
        } else {
            $aiChoice = $this->turnOrder % 5;
            try {
                switch ($aiChoice) {
                    case 1:
                        $aiName = "Gemini";
                        $aiResponse = $this->callGeminiApi($fullPrompt, $systemPrompt);
                        break;
                    case 2:
                        $aiName = "Groq (Llama 3.1)";
                        $aiResponse = $this->callGroqApi($fullPrompt, $systemPrompt);
                        break;
                    case 3:
                        $aiName = "Mistral";
                        $aiResponse = $this->callMistralApi($fullPrompt, $systemPrompt);
                        break;
                    case 4:
                        $aiName = "Cohere";
                        $aiResponse = $this->callCohereApi($fullPrompt, $systemPrompt);
                        break;
                    case 0:
                        $aiName = "OpenRouter (NVIDIA Nemotron)";
                        $aiResponse = $this->callOpenRouterApi($fullPrompt, $systemPrompt);
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Error $aiName: " . $e->getMessage());
                $aiResponse = "Mohon maaf, $aiName mengalami gangguan koneksi. (Error: " . $e->getMessage() . ")";
            }
        }

        // ==========================================
        // SIMPAN KE DATABASE
        // ==========================================
        $contentToSave = "";
        if ($this->stance === 'kesimpulan') $contentToSave = $aiResponse;
        elseif ($this->stance === 'ai_answer') $contentToSave = "🤖 [Jawaban Panelis AI]\n\n" . $aiResponse;
        else $contentToSave = "🤖 [Ditenagai oleh $aiName]\n\n" . $aiResponse;

        Argument::create([
            'debate_room_id' => $this->roomId,
            'participant_id' => null,
            'stance' => $this->stance,
            'turn_order' => $this->turnOrder,
            'content' => $contentToSave
        ]);

        // ==========================================
        // SCHEDULING RONDE BERIKUTNYA
        // ==========================================
        if ($this->stance === 'kesimpulan') {
            $room->update(['status' => 'archived']);
        } elseif ($this->stance !== 'ai_answer') { // Jangan jadwalkan argumen baru jika ini hanya sesi tanya-jawab lanjutan
            $maxTurns = $room->max_rounds * 2;
            if ($this->turnOrder < $maxTurns) {
                $nextStance = ($this->stance === 'pro') ? 'kontra' : 'pro';
                ProcessAiDebate::dispatch($this->roomId, $this->turnOrder + 1, $nextStance)->delay(now()->addSeconds(3));
            } else {
                ProcessAiDebate::dispatch($this->roomId, $this->turnOrder + 1, 'kesimpulan')->delay(now()->addSeconds(4));
            }
        }
    }

    // ==========================================
    // FUNGSI-FUNGSI API AI
    // ==========================================
    private function callGeminiApi($prompt, $systemInstruction) {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey;
        $response = Http::post($url, [
            'system_instruction' => ['parts' => ['text' => $systemInstruction]],
            'contents' => [['parts' => [['text' => $prompt]]]]
        ]);
        if ($response->successful()) return $response->json('candidates.0.content.parts.0.text');
        throw new \Exception("Gagal: " . $response->body());
    }

    private function callGroqApi($prompt, $systemInstruction) {
        $apiKey = env('GROQ_API_KEY');
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'llama-3.1-8b-instant', 
            'messages' => [['role' => 'system', 'content' => $systemInstruction], ['role' => 'user', 'content' => $prompt]]
        ]);
        if ($response->successful()) return $response->json('choices.0.message.content');
        throw new \Exception("Gagal: " . $response->body());
    }

    private function callMistralApi($prompt, $systemInstruction) {
        $apiKey = env('MISTRAL_API_KEY');
        $url = "https://api.mistral.ai/v1/chat/completions";
        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'mistral-small-latest',
            'messages' => [['role' => 'system', 'content' => $systemInstruction], ['role' => 'user', 'content' => $prompt]]
        ]);
        if ($response->successful()) return $response->json('choices.0.message.content');
        throw new \Exception("Gagal: " . $response->body());
    }

    private function callCohereApi($prompt, $systemInstruction) {
        $apiKey = env('COHERE_API_KEY');
        $url = "https://api.cohere.ai/v1/chat";
        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'command-r-08-2024',
            'message' => $prompt,
            'preamble' => $systemInstruction 
        ]);
        if ($response->successful()) return $response->json('text');
        throw new \Exception("Gagal: " . $response->body());
    }

    private function callOpenRouterApi($prompt, $systemInstruction) {
        $apiKey = env('OPENROUTER_API_KEY');
        $url = "https://openrouter.ai/api/v1/chat/completions";
        $response = Http::withToken($apiKey)->post($url, [
            'model' => 'nvidia/nemotron-3-nano-30b-a3b:free', 
            'messages' => [['role' => 'system', 'content' => $systemInstruction], ['role' => 'user', 'content' => $prompt]]
        ]);
        if ($response->successful()) return $response->json('choices.0.message.content');
        throw new \Exception("Gagal: " . $response->body());
    }
}