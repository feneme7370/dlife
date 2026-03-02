<?php

namespace App\Imports;

use Illuminate\Support\Str;
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
                'title' => $row['title'],
                'slug' => $row['slug'] ?? Str::slug($row['title']),
                'original_title' => $row['original_title'],
                'synopsis' => $row['synopsis'],
                'release_date' => $row['release_date'],
                'number_collection' => $row['number_collection'],
                'pages' => $row['pages'],

                'summary' => $row['summary'],
                'summary_clear' => $row['summary_clear'],
                'notes' => $row['notes'],
                'notes_clear' => $row['notes_clear'],

                'is_favorite' => $row['is_favorite'] ?? false,
                'is_abandonated' => $row['is_abandonated'] ?? false,
                'rating' => $row['rating'],

                'cover_image' => $row['cover_image'],
                'cover_image_url' => $row['cover_image_url'],
                'user_id' => $row['user_id'],
            ]
        );

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($book, $row['book_subjects'], \App\Models\Page\Subject::class, 'book_subjects');
        $this->syncRelation($book, $row['book_collections'], \App\Models\Page\Collection::class, 'book_collections');
        $this->syncRelation($book, $row['book_genres'], \App\Models\Page\BookGenre::class, 'book_book_genres');
        $this->syncRelation($book, $row['book_tags'], \App\Models\Page\Btag::class, 'book_btags');

        // 3️⃣ Restaurar lecturas (one-to-many)
        $book->book_reads()->delete(); // 🔥 importante en restore

        if (!empty($row['book_reads'])) {

            $reads = collect(explode('|', $row['book_reads']))
                ->map(fn($item) => trim($item))
                ->filter();

            foreach ($reads as $read) {

                [$start, $end] = array_map('trim', explode('→', $read));

                \App\Models\Page\BookRead::create([
                    'book_id' => $book->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'start_read' => $start,
                    'end_read' => $end === 'En progreso' ? null : $end,
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
                ['slug' => Str::slug($name), 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => \Illuminate\Support\Facades\Auth::id()]
            );

            $ids[] = $model->id;
        }

        $book->$relationName()->sync($ids);
    }
}