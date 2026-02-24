<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'slug',

        'name_sub',
        'slug_sub',

        'birthdate', 
        'country',

        'description', 

        'cover_image',
        'cover_image_url',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
}
