<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debate_room_user', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User & Ruangan (Cascade agar jika ruangan dihapus, data anggotanya ikut terhapus)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debate_room_id')->constrained()->cascadeOnDelete();
            
            // Kolom Jabatan Kontekstual di ruangan tersebut
            $table->string('role')->default('audience'); 
            
            // Mencegah 1 orang punya 2 jabatan ganda di ruangan yang sama
            $table->unique(['user_id', 'debate_room_id']);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debate_room_user');
    }
};
