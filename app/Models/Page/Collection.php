<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'slug',

        'description', 
        'books_amount', 
        'movies_amount', 

        'cover_image',
        'cover_image_url',

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
        return $this->belongsToMany(\App\Models\Page\Book::class, 'book_collection')
                    ->withTimestamps();
    }
}
