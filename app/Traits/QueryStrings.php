<?php

namespace App\Traits;

trait QueryStrings
{
    //////////////////////////////////////////////////////////////////// FUNCIONES PARA FILTRAR
    // mostrar variables en queryString
    protected function queryString(){
        return [
        'search' => [ 'as' => 'q' ],
        
        'status_read' => [ 'as' => 'r' ],
        
        'collection_selected' => [ 'as' => 'c' ],
        'subject_selected' => [ 'as' => 'a' ],
        'genre_selected' => [ 'as' => 'g' ],
        
        'category_selected' => [ 'as' => 'cat' ],
        'tag_selected' => [ 'as' => 'tag' ],
        
        'star_selected' => [ 'as' => 'star' ],
        ];
    }
}