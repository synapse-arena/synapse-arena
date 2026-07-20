<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebateRoom extends Model
{
    // Izinkan semua kolom diisi secara massal (Mass Assignment)
    protected $fillable = ['topic', 'mode', 'prompter_id', 'status', 'max_rounds'];

    // Relasi: Ruangan ini milik 1 Prompter (User)
    public function prompter()
    {
        return $this->belongsTo(User::class, 'prompter_id');
    }

    // Relasi: Ruangan ini punya Banyak Partisipan (AI)
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    // Relasi: Ruangan ini punya Banyak Transkrip Argumen
    public function arguments()
    {
        return $this->hasMany(Argument::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

}
