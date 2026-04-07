<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'release_year', 

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos libros para relacionarse
    public function games(){
        return $this->belongsToMany(\App\Models\Page\Game::class, 'game_platform')
                    ->withTimestamps();
    }
}
