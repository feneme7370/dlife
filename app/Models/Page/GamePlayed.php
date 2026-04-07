<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class GamePlayed extends Model
{
    protected $fillable = [
        'start_played',
        'end_played',
        'game_id',
        'game_uuid',
        'user_id', 
    ];

    protected $casts = [
        'start_played' => 'date',
        'end_played' => 'date',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // pertenece a un libro
    public function game()
    {
        return $this->belongsTo(\App\Models\Page\Game::class);
    }
}
