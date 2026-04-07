<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SeriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // // 1️⃣ Crear o actualizar por UUID
        // $serie = \App\Models\Page\Serie::updateOrCreate(
        //     ['uuid' => $row['uuid']], // clave única
        //     [
        //         'title' => \Illuminate\Support\Str::title(trim($row['title'])),
        //         'slug' => \Illuminate\Support\Str::slug($row['title'] . '-' . \Illuminate\Support\Facades\Auth::id() . '-' . \Illuminate\Support\Str::random(6)),
        //         'original_title' => $row['original_title'],
        //         'synopsis' => $row['synopsis'],
        //         'start_date' => $row['start_date'],
        //         'end_date' => $row['end_date'],
        //         'number_collection' => $row['number_collection'] ?? 1,
        //         'seasons' => $row['seasons'] ?? 0,
        //         'episodes' => $row['episodes'] ?? 0,
        //         'type' => $row['type'] ?? 1,

        //         'summary' => $row['summary'],
        //         'summary_clear' => $row['summary_clear'],
        //         'notes' => $row['notes'],
        //         'notes_clear' => $row['notes_clear'],

        //         'is_favorite' => $row['is_favorite'] ?? false,
        //         'is_abandonated' => $row['is_abandonated'] ?? false,
        //         'is_public' => $row['is_public'] ?? false,
        //         'rating' => $row['rating'] ?? 0,

        //         'cover_image' => $row['cover_image'],
        //         'cover_image_url' => $row['cover_image_url'],
        //         'user_id' => $row['user_id'] ?? Auth::id(),
        //         'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
        //     ]
        // );
// 1. Buscamos si la serie ya existe por su UUID
$serie = \App\Models\Page\Serie::where('uuid', $row['uuid'])->first();

// 2. Definimos los datos base (excluyendo campos que generan conflicto)
$data = [
    'title'             => \Illuminate\Support\Str::title(trim($row['title'])),
    'original_title'    => $row['original_title'],
    'synopsis'          => $row['synopsis'],
    'start_date'        => $row['start_date'],
    'end_date'          => $row['end_date'],
    'number_collection' => $row['number_collection'] ?? 1,
    'seasons'           => $row['seasons'] ?? 0,
    'episodes'          => $row['episodes'] ?? 0,
    'type'              => $row['type'] ?? 1,
    'summary'           => $row['summary'],
    'summary_clear'     => $row['summary_clear'],
    'notes'             => $row['notes'],
    'notes_clear'       => $row['notes_clear'],
    'is_favorite'       => $row['is_favorite'] ?? false,
    'is_abandonated'    => $row['is_abandonated'] ?? false,
    'is_public'         => $row['is_public'] ?? false,
    'rating'            => $row['rating'] ?? 0,
    'cover_image'       => $row['cover_image'],
    'cover_image_url'   => $row['cover_image_url'],
    'user_id'           => $row['user_id'] ?? Auth::id(),
];

if (!$serie) {
    // --- ACCIÓN: CREAR ---
    // Agregamos el identificador y el slug único solo al crear
    $data['uuid'] = $row['uuid'] ?? \Illuminate\Support\Str::random(24);
    $data['slug'] = \Illuminate\Support\Str::slug($row['title'] . '-' . Auth::id()) . '-' . \Illuminate\Support\Str::random(6);
    
    $serie = \App\Models\Page\Serie::create($data);
} else {
    // --- ACCIÓN: ACTUALIZAR ---
    // Actualizamos el registro encontrado. 
    // El slug se mantiene intacto para no romper enlaces existentes.
    $serie->update($data);
}

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($serie, $row['subjects'], \App\Models\Page\Subject::class, 'subjects');
        $this->syncRelation($serie, $row['collections'], \App\Models\Page\Collection::class, 'collections');
        $this->syncRelation($serie, $row['genres'], \App\Models\Page\Genre::class, 'genres');
        $this->syncRelation($serie, $row['tags'], \App\Models\Page\Tag::class, 'tags');

        // 3️⃣ Restaurar lecturas (one-to-many)
        $serie->views()->delete(); // 🔥 importante en restore

        if (!empty($row['views'])) {

            $views = collect(explode('|', $row['views']))
                ->map(fn($item) => trim($item))
                ->filter();

            foreach ($views as $view) {

                [$start, $end] = array_map('trim', explode('→', $view));

                \App\Models\Page\SerieView::create([
                    'serie_id' => $serie->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'start_view' => \Carbon\Carbon::parse($start)->format('Y-m-d'),
                    'end_view' => $end === 'En progreso' ? null : \Carbon\Carbon::parse($end)->format('Y-m-d'),
                ]);
            }
        }

        return $serie;
    }

    private function syncRelation($serie, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $serie->$relationName()->sync([]); // limpia si viene vacío
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
                    'slug' => \Illuminate\Support\Str::slug($name . '-' . \Illuminate\Support\Facades\Auth::id()), 
                    'tag_type' => 'series',
                    'genre_type' => 'visual',
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $serie->$relationName()->sync($ids);
    }
}