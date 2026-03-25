<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'title',
        'slug',

        'description',

        'ingredients',
        'ingredients_clear',

        'instructions',
        'instructions_clear',

        'is_public',

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos collections para relacionarse
    public function categories(){
        return $this->belongsToMany(\App\Models\Page\Category::class, 'recipe_category')
                    ->withTimestamps();
    }

    // tiene muchos collections para relacionarse
    public function tags(){
        return $this->belongsToMany(\App\Models\Page\Tag::class, 'recipe_tag')
                    ->withTimestamps();
    }
}
