<?php

namespace App\Traits;

use App\Models\Page\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait WithCollections
{
    public $name_collection;
    public $books_amount_collection;
    public $movies_amount_collection;
    public $seasons_amount_collection;

    public function storeCollection($selectedProperty)
    {
        $this->name_collection = Str::title(trim($this->name_collection));

        $this->validate([
            'name_collection' => ['required', 'string', 'max:255'],
            'books_amount_collection' => ['nullable', 'numeric'],
            'movies_amount_collection' => ['nullable', 'numeric'],
            'seasons_amount_collection' => ['nullable', 'numeric'],
        ]);

        $collection = Collection::firstOrCreate(
            [
                'name' => Str::title(trim($this->name_collection)),
                'user_id' => Auth::id(),
            ],
            [
                'books_amount' => $this->books_amount_collection ?? 0,
                'movies_amount' => $this->movies_amount_collection ?? 0,
                'seasons_amount' => $this->seasons_amount_collection ?? 0,
                'slug' => Str::slug(trim($this->name_collection) . '-' . Auth::id()),
                'uuid' => Str::random(24),
            ]
        );

        $this->reset(
            'name_collection',
            'books_amount_collection',
            'movies_amount_collection',
            'seasons_amount_collection'
        );

        if (method_exists($this, 'collections')) {
            $this->collections();
        }

        if (property_exists($this, $selectedProperty)) {
            $this->{$selectedProperty}[] = $collection->id;
        }

        $this->modal('add-collection')->close();
    }
}