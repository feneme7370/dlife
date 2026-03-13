<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Bltag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // tiene muchos libros para relacionarse
    public function blogs(){
        return $this->belongsToMany(\App\Models\Page\Blog::class, 'blog_bltag')
                    ->withTimestamps();
    }
}
