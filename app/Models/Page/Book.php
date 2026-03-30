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
        'type',
        
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

    protected $casts = [
        'number_collection' => 'decimal:2',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos subjects para relacionarse
    public function subjects(){
        return $this->belongsToMany(\App\Models\Page\Subject::class, 'book_subject')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function collections(){
        return $this->belongsToMany(\App\Models\Page\Collection::class, 'book_collection')
                    ->withTimestamps();
    }

    // tiene muchos generos para relacionarse
    public function genres(){
        return $this->belongsToMany(\App\Models\Page\Genre::class, 'book_genre')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function tags(){
        return $this->belongsToMany(\App\Models\Page\Tag::class, 'book_tag')
                    ->withTimestamps();
    }

    // tiene muchas lecturas
    public function reads(){
        return $this->hasMany(\App\Models\Page\BookRead::class);
    }

    public function languages()
    {
        return $this->belongsToMany(\App\Models\Page\Language::class, 'book_language');
    }

    public function readingFormats()
    {
        return $this->belongsToMany(\App\Models\Page\ReadingFormat::class, 'book_reading_format');
    }

    // tipo de libro
    public static function type(){
        return [
            1 => 'Libro 📖', 
            2 => 'Manga 📚',
        ];
    }
}
