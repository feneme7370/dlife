<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $fillable = [
        'day',
        'status',

        'title', 
        'content',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // valoraciones en estrellas para cada libro
    public static function humor_status()
    {
        return [
            0  => 'Sin datos ⚪',
            1  => 'Muy mal dia 😞',
            2  => 'Mal dia 😔',
            3  => 'Dia dificil 😣',
            4  => 'Bajon 😕',
            5  => 'Dia normal 😐',
            6  => 'Buen dia 🙂',
            7  => 'Muy buen dia 😊',
            8  => 'Gran dia 😄',
            9  => 'Excelente dia 😁',
            10 => 'Dia inolvidable 🤩',
        ];
    }
}
