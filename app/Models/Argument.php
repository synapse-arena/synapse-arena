<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Argument extends Model
{
    // Mengizinkan Laravel untuk mengisi kolom-kolom ini
    protected $fillable = [
        'debate_room_id',
        'participant_id',
        'stance',
        'content',
        'turn_order',
    ];
}