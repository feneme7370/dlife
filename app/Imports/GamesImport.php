<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GamesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {

// 1. Buscamos si la película ya existe por su UUID
$game = \App\Models\Page\Game::where('uuid', $row['uuid'])->first();

// 2. Preparamos el array con los datos generales
$data = [
    'title'             => \Illuminate\Support\Str::title(trim($row['title'])),
    'original_title'    => $row['original_title'],
    'synopsis'          => $row['synopsis'],
    'release_date'      => $row['release_date'],

    'summary'           => $row['summary'],
    'summary_clear'     => $row['summary_clear'],
    'notes'             => $row['notes'],
    'notes_clear'       => $row['notes_clear'],

    'is_favorite'       => $row['is_favorite'] ?? false,
    'is_abandonated'    => $row['is_abandonated'] ?? false,
    'is_public'         => $row['is_public'] ?? false,

    'rating'            => $row['rating'] ?? 0,
    'type'              => $row['type'] ?? 1,

    'cover_image'       => $row['cover_image'],
    'cover_image_url'   => $row['cover_image_url'],
    'user_id'           => $row['user_id'] ?? Auth::id(),
];

if (!$game) {
    // --- LÓGICA PARA CREACIÓN ---
    // Si no existe, asignamos el UUID (del Excel o nuevo) y generamos el slug único
    $data['uuid'] = $row['uuid'] ?? \Illuminate\Support\Str::random(24);
    $data['slug'] = \Illuminate\Support\Str::slug($row['title'] . '-' . Auth::id());
    
    $game = \App\Models\Page\Game::create($data);
} else {
    // --- LÓGICA PARA ACTUALIZACIÓN ---
    // Si ya existe, actualizamos los datos. 
    // Omitimos el 'slug' para no romper la restricción UNIQUE si el título no cambió,
    // y omitimos el 'uuid' porque es lo que usamos para encontrarlo.
    $game->update($data);
}

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($game, $row['subjects'], \App\Models\Page\Subject::class, 'subjects');
        $this->syncRelation($game, $row['collections'], \App\Models\Page\Collection::class, 'collections');
        $this->syncRelation($game, $row['categories'], \App\Models\Page\Genre::class, 'categories');
        $this->syncRelation($game, $row['tags'], \App\Models\Page\Tag::class, 'tags');
        $this->syncRelation($game, $row['platforms'], \App\Models\Page\Tag::class, 'platforms');

        // 3️⃣ Restaurar lecturas (one-to-many)
        $game->playeds()->delete(); // 🔥 importante en restore

        if (!empty($row['playeds'])) {

            $playeds = collect(explode('|', $row['playeds']))
                ->map(fn($item) => trim($item))
                ->filter();

            foreach ($playeds as $played) {

                [$start, $end] = array_map('trim', explode('→', $played));

                \App\Models\Page\GamePlayed::create([
                    'game_id' => $game->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'start_played' => \Carbon\Carbon::parse($start)->format('Y-m-d'),
                    'end_played' => $end === 'En progreso' ? null : \Carbon\Carbon::parse($end)->format('Y-m-d'),
                ]);
            }
        }

        return $game;
    }

    private function syncRelation($game, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $game->$relationName()->sync([]); // limpia si viene vacío
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
                    'tag_type' => 'games',
                    'category_type' => 'games',
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $game->$relationName()->sync($ids);
    }
}