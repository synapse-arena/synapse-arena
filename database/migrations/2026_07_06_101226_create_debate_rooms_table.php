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
        Schema::create('debate_rooms', function (Blueprint $table) {
            $table->id();
            // Berelasi ke tabel users (Prompter)
            $table->string('topic');
            $table->integer('max_rounds')->default(3);
            // Status ruangan menggunakan tipe Enum agar kaku & aman
            $table->enum('status', ['draft', 'live', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debate_rooms');
    }
};
