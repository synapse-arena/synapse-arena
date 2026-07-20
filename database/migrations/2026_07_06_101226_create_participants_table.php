<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            // Berelasi ke tabel debate_rooms
            $table->foreignId('debate_room_id')->constrained()->cascadeOnDelete();
            $table->string('ai_model_name'); // Contoh: "Gemini 1.5" atau "Llama 3"
            $table->enum('stance', ['pro', 'contra']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
