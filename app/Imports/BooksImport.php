<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BooksImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Crear o actualizar por UUID
        $book = \App\Models\Page\Book::updateOrCreate(
            ['uuid' => $row['uuid']], // clave única
            [
                'title' => \Illuminate\Support\Str::title(trim($row['title'])),
                'slug' => \Illuminate\Support\Str::slug($row['title'] . '-' . \Illuminate\Support\Facades\Auth::id()),
                'original_title' => $row['original_title'],
                'synopsis' => $row['synopsis'],
                'release_date' => $row['release_date'],
                'number_collection' => $row['number_collection'] ?? 1,
                'pages' => $row['pages'] ?? 1,
                'type' => $row['type'] ?? 1,

                'summary' => $row['summary'],
                'summary_clear' => $row['summary_clear'],
                'notes' => $row['notes'],
                'notes_clear' => $row['notes_clear'],

                'is_favorite' => $row['is_favorite'] ?? false,
                'is_abandonated' => $row['is_abandonated'] ?? false,
                'is_public' => $row['is_public'] ?? false,
                'rating' => $row['rating'] ?? 0,

                'cover_image' => $row['cover_image'],
                'cover_image_url' => $row['cover_image_url'],
                'user_id' => $row['user_id'] ?? Auth::id(),
                'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
            ]
        );

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($book, $row['languages'], \App\Models\Page\Language::class, 'languages');
        $this->syncRelation($book, $row['reading_formats'], \App\Models\Page\ReadingFormat::class, 'readingFormats');
        $this->syncRelation($book, $row['subjects'], \App\Models\Page\Subject::class, 'subjects');
        $this->syncRelation($book, $row['collections'], \App\Models\Page\Collection::class, 'collections');
        $this->syncRelation($book, $row['genres'], \App\Models\Page\BookGenre::class, 'genres');
        $this->syncRelation($book, $row['tags'], \App\Models\Page\Btag::class, 'tags');

        // 3️⃣ Restaurar lecturas (one-to-many)
        $book->reads()->delete(); // 🔥 importante en restore

        if (!empty($row['reads'])) {

            $reads = collect(explode('|', $row['reads']))
                ->map(fn($item) => trim($item))
                ->filter();

            foreach ($reads as $read) {

                [$start, $end] = array_map('trim', explode('→', $read));

                \App\Models\Page\BookRead::create([
                    'book_id' => $book->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'start_read' => \Carbon\Carbon::parse($start)->format('Y-m-d'),
                    'end_read' => $end === 'En progreso' ? null : \Carbon\Carbon::parse($end)->format('Y-m-d'),
                ]);
            }
        }

        return $book;
    }

    private function syncRelation($book, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $book->$relationName()->sync([]); // limpia si viene vacío
            return;
        }

        $items = collect(explode(',', $column))
            ->map(fn($name) => trim($name))
            ->filter();

        $ids = [];

        foreach ($items as $name) {

            $model = $modelClass::firstOrCreate(
                ['name' => $name],
                [
                    'name_general' => 'Sin categria', 
                    'slug_general' => \Illuminate\Support\Str::slug('Sin categoria' . '-' . \Illuminate\Support\Facades\Auth::id() . '-' . \Illuminate\Support\Str::random(6)), 
                    'slug' => \Illuminate\Support\Str::slug($name . '-' . \Illuminate\Support\Facades\Auth::id() . '-' . \Illuminate\Support\Str::random(6)), 
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $book->$relationName()->sync($ids);
    }
}