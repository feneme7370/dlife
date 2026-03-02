<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Btag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos libros para relacionarse
    public function books(){
        return $this->belongsToMany(\App\Models\Page\Book::class, 'book_btag')
                    ->withTimestamps();
    }
}
