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
        Schema::table('debate_rooms', function (Blueprint $table) {
            // Tambahkan kolom mode dengan default 'debate'
            $table->enum('mode', ['debate', 'discussion'])->default('debate')->after('topic');
        });
    }

    public function down(): void
    {
        Schema::table('debate_rooms', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
