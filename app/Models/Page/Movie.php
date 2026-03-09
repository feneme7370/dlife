<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'original_title',
        'synopsis',
        'release_date',

        'number_collection',
        'runtime',
        'type',

        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',
        'is_public',

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
        return $this->belongsToMany(\App\Models\Page\Subject::class, 'movie_subject')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function collections(){
        return $this->belongsToMany(\App\Models\Page\Collection::class, 'movie_collection')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function genres(){
        return $this->belongsToMany(\App\Models\Page\Mgenre::class, 'movie_mgenre')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function tags(){
        return $this->belongsToMany(\App\Models\Page\Mtag::class, 'movie_mtag')
                    ->withTimestamps();
    }

    // tiene muchas lecturas
    public function views(){
        return $this->hasMany(\App\Models\Page\MovieView::class);
    }

    // tipo de pelicula
    public static function type(){
        return [
            1 => 'Película 🎬',
            2 => 'OVA 📀',
            3 => 'Cortometraje 🎞',
        ];
    }
}
