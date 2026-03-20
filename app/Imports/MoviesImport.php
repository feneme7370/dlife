<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MoviesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Crear o actualizar por UUID
        $movie = \App\Models\Page\Movie::updateOrCreate(
            [
                'uuid' => $row['uuid'],
            ], // clave única
            [
                'title' => \Illuminate\Support\Str::title(trim($row['title'])),
                'slug' => \Illuminate\Support\Str::slug($row['title'] . '-' . \Illuminate\Support\Facades\Auth::id()) . '-' . \Illuminate\Support\Str::random(6),
                'original_title' => $row['original_title'],
                'synopsis' => $row['synopsis'],
                'release_date' => $row['release_date'],
                'number_collection' => $row['number_collection'] ?? 1,
                'runtime' => $row['runtime'] ?? 1,
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
        $this->syncRelation($movie, $row['subjects'], \App\Models\Page\Subject::class, 'subjects');
        $this->syncRelation($movie, $row['collections'], \App\Models\Page\Collection::class, 'collections');
        $this->syncRelation($movie, $row['genres'], \App\Models\Page\Mgenre::class, 'genres');
        $this->syncRelation($movie, $row['tags'], \App\Models\Page\Mtag::class, 'tags');

        // 3️⃣ Restaurar lecturas (one-to-many)
        $movie->views()->delete(); // 🔥 importante en restore

        if (!empty($row['views'])) {

            $views = collect(explode('|', $row['views']))
                ->map(fn($item) => trim($item))
                ->filter();

            foreach ($views as $view) {

                [$start, $end] = array_map('trim', explode('→', $view));

                \App\Models\Page\MovieView::create([
                    'movie_id' => $movie->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'start_view' => \Carbon\Carbon::parse($start)->format('Y-m-d'),
                    'end_view' => $end === 'En progreso' ? null : \Carbon\Carbon::parse($end)->format('Y-m-d'),
                ]);
            }
        }

        return $movie;
    }

    private function syncRelation($movie, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $movie->$relationName()->sync([]); // limpia si viene vacío
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
                    'slug_general' => \Illuminate\Support\Str::slug('Sin categoria' . '-' . \Illuminate\Support\Facades\Auth::id()), 
                    'slug' => \Illuminate\Support\Str::slug($name . '-' . \Illuminate\Support\Facades\Auth::id()), 
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $movie->$relationName()->sync($ids);
    }
}