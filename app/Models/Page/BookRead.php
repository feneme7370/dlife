<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class BookRead extends Model
{
    protected $fillable = [
        'start_read',
        'end_read',
        'book_id',
        'book_uuid',
        'user_id', 
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // pertenece a un libro
    public function book()
    {
        return $this->belongsTo(\App\Models\Page\Book::class, 'user_id', 'id');
    }
}
