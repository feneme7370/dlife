<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_type', // recipes, diaries, etc

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
    public function diaries(){
        return $this->belongsToMany(\App\Models\Page\Diary::class, 'diary_category')
                    ->withTimestamps();
    }

    // tiene muchos recipes para relacionarse
    public function recipes(){
        return $this->belongsToMany(\App\Models\Page\Recipe::class, 'recipe_category')
                    ->withTimestamps();
    }

}
