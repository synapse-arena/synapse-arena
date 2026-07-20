<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DebateRoom;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat 3 Akun Khusus Demo
        $prompter = User::create([
            'name' => 'Kreator (Prompter)',
            'email' => 'prompter@demo.com',
            'password' => Hash::make('password')
        ]);

        $audience = User::create([
            'name' => 'Pengunjung (Audience)',
            'email' => 'audience@demo.com',
            'password' => Hash::make('password')
        ]);

        $moderator = User::create([
            'name' => 'Pengawas (Moderator)',
            'email' => 'moderator@demo.com',
            'password' => Hash::make('password')
        ]);

        // 2. Buat Ruang Debat Pameran
        $room = DebateRoom::create([
            'topic' => 'Mosi Pameran: Apakah AI Mengancam Masa Depan Pekerjaan IT?',
            'max_rounds' => 3,
            'status' => 'live' 
        ]);

        // 3. Masukkan ke Pivot Table beserta jabatan spesifiknya
        $room->users()->attach([
            $prompter->id => ['role' => 'prompter'],
            $audience->id => ['role' => 'audience'],
            $moderator->id => ['role' => 'moderator'],
        ]);
    }
}