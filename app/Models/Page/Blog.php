<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [

        'title',
        'slug',
        'excerpt',

        'content',
        'content_clear',

        'type',
        'entry_type',
        'year',
        
        'is_public',

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'year' => 'integer',
    ];
 
    // pertenece a un usuario
    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos collections para relacionarse
    public function tags(){
        return $this->belongsToMany(\App\Models\Page\Tag::class, 'blog_tag')
                    ->withTimestamps();
    }

    public static function types(){
        return [

            'tip' => 'Tip 💡',
            'talk' => 'Charla 🎤',
            'idea' => 'Idea 🧠',
            'reflection' => 'Reflexión ✍️',
            'summary' => 'Resumen 📚',
            'random' => 'Random 🎲',

        ];
    }

    public static function bullet_types(){
        return [

            'travel' => 'Viajes ✈️',
            'about_me' => 'Sobre mí 🙋‍♂️',
            'activities' => 'Actividades 🎯',
            'wishlist' => 'Cosas que quiero 🛍️',
            'planner' => 'Planner 🗓️',
            'goals' => 'Metas 🎯',
            'memories' => 'Recuerdos 📸',
            'habits' => 'Hábitos 🔁',
            'recipes' => 'Recetas 🍝',
            'media' => 'Películas, series y libros 🎬',

        ];
    }

    public static function entryTypes(){
        return [
            'blog' => 'Blog 📝',
            'bullet' => 'Bullet 🗒️',
            'podcast' => 'Podcast 🎧',
            'video' => 'Video 🎥',
        ];
    }

}
