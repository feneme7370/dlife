<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class MovieView extends Model
{
    protected $fillable = [
        'start_view',
        'end_view',
        'movie_id',
        'movie_uuid',
        'user_id', 
    ];

    protected $casts = [
        'start_view' => 'date',
        'end_view' => 'date',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // pertenece a un libro
    public function movie()
    {
        return $this->belongsTo(\App\Models\Page\Movie::class, 'user_id', 'id');
    }
}
