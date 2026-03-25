<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'genre_type', // books, movies, series, etc

        'description', 

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos libros para relacionarse
    public function books(){
        return $this->belongsToMany(\App\Models\Page\Book::class, 'book_genre')
                    ->withTimestamps();
    }

    // tiene muchos movies para relacionarse
    public function movies(){
        return $this->belongsToMany(\App\Models\Page\Movie::class, 'movie_genre')
                    ->withTimestamps();
    }

    // tiene muchos series para relacionarse
    public function series(){
        return $this->belongsToMany(\App\Models\Page\Serie::class, 'serie_genre')
                    ->withTimestamps();
    }
}
