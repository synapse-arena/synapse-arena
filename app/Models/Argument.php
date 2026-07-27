<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Argument extends Model
{
<<<<<<< HEAD
    // Mengizinkan Laravel untuk mengisi kolom-kolom ini
    protected $fillable = [
        'debate_room_id',
        'participant_id',
        'stance',
        'content',
        'turn_order',
    ];
=======
    protected $fillable = [
        'debate_room_id', 
        'participant_id', 
        'user_id', // Ini yang baru saja kita tambahkan di migrasi
        'content', 
        'turn_order', 
        'stance'
    ];

    // Relasi ke AI yang bicara
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    // Relasi ke Prompter (Manusia) yang bicara
    public function user()
    {
        return $this->belongsTo(User::class);
    }
>>>>>>> 42e2ef447ae4d631f693b47ae4dc0d0b538ab45b
}