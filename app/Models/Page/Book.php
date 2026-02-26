<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'original_title',
        'synopsis',
        'release_date',
        'number_collection',
        'pages',
        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',

        'start_read',
        'end_read',

        'rating',

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos subjects para relacionarse
    public function book_subjects(){
        return $this->belongsToMany(\App\Models\Page\Subject::class, 'book_subject')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function book_collections(){
        return $this->belongsToMany(\App\Models\Page\Collection::class, 'book_collection')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function book_book_genres(){
        return $this->belongsToMany(\App\Models\Page\BookGenre::class, 'book_book_genre')
                    ->withTimestamps();
    }

    // tiene muchas lecturas
    public function book_reads(){
        return $this->hasMany(BookRead::class);
    }
}
