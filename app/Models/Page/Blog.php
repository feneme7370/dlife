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
        'is_public',

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
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

}
