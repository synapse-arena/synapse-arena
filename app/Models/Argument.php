<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Argument extends Model
{
    protected $fillable = [
        'debate_room_id', 
        'participant_id', 
        'user_id', 
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
}