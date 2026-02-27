<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class DiaryTemplate extends Model
{
    protected $fillable = [
        'title', 
        'content',

        'uuid',
        'user_id',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
}
