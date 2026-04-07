<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'original_title',
        'synopsis',
        'release_date',
        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',
        'is_public',

        'type',
        'rating',

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    protected $casts = [
        'release_date' => 'integer',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos subjects para relacionarse
    public function subjects(){
        return $this->belongsToMany(\App\Models\Page\Subject::class, 'game_subject')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function collections(){
        return $this->belongsToMany(\App\Models\Page\Collection::class, 'game_collection')
                    ->withTimestamps();
    }

    // tiene muchos generos para relacionarse
    public function categories(){
        return $this->belongsToMany(\App\Models\Page\Category::class, 'game_category')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function tags(){
        return $this->belongsToMany(\App\Models\Page\Tag::class, 'game_tag')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function platforms(){
        return $this->belongsToMany(\App\Models\Page\Platform::class, 'game_platform')
                    ->withTimestamps();
    }

    // tiene muchas lecturas
    public function playeds(){
        return $this->hasMany(\App\Models\Page\GamePlayed::class);
    }
}
