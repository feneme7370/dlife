<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class SerieView extends Model
{
    protected $fillable = [
        'start_view',
        'end_view',
        'serie_id',
        'serie_uuid',
        'user_id', 
    ];

    protected $casts = [
        'start_view' => 'date',
        'end_view' => 'date',
    ];

    // pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // pertenece a un libro
    public function serie()
    {
        return $this->belongsTo(\App\Models\Page\Serie::class, 'user_id', 'id');
    }
}
