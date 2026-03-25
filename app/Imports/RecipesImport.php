<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecipesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Crear o actualizar por UUID
        $recipe = \App\Models\Page\Recipe::updateOrCreate(
            ['uuid' => $row['uuid']], // clave única
            [
                'title' => \Illuminate\Support\Str::title(trim($row['title'])),
                'slug' => \Illuminate\Support\Str::slug($row['title'] . '-' . \Illuminate\Support\Facades\Auth::id() . '-' . \Illuminate\Support\Str::random(6)),
                'description' => $row['description'] ?? '',

                'ingredients' => $row['ingredients'],
                'ingredients_clear' => $row['ingredients_clear'],
                'instructions' => $row['instructions'],
                'instructions_clear' => $row['instructions_clear'],

                'is_public' => $row['is_public'] ?? false,

                'cover_image' => $row['cover_image'],
                'cover_image_url' => $row['cover_image_url'],
                'user_id' => $row['user_id'] ?? Auth::id(),
                'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
            ]
        );

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($recipe, $row['categories'], \App\Models\Page\Category::class, 'categories');
        $this->syncRelation($recipe, $row['tags'], \App\Models\Page\Tag::class, 'tags');

        return $recipe;
    }

    private function syncRelation($recipe, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $recipe->$relationName()->sync([]); // limpia si viene vacío
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
                    'tag_type' => 'recipes',
                    'category_type' => 'recipes',
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $recipe->$relationName()->sync($ids);
    }
}