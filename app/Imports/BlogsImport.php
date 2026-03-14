<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BlogsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Crear o actualizar por UUID
        $blog = \App\Models\Page\Blog::updateOrCreate(
            ['uuid' => $row['uuid']], // clave única
            [
                'title' => \Illuminate\Support\Str::title(trim($row['title'])),
                'slug' => \Illuminate\Support\Str::slug($row['title'] . '-' . \Illuminate\Support\Str::random(4)),
                'excerpt' => $row['excerpt'] ?? '',
                'type' => $row['type'] ?? '',

                'content' => $row['content'],
                'content_clear' => $row['content_clear'],

                'is_public' => $row['is_public'] ?? false,

                'cover_image' => $row['cover_image'],
                'cover_image_url' => $row['cover_image_url'],
                'user_id' => $row['user_id'] ?? Auth::id(),
                'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
            ]
        );

        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($blog, $row['tags'], \App\Models\Page\Bltag::class, 'tags');

        return $blog;
    }

    private function syncRelation($blog, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $blog->$relationName()->sync([]); // limpia si viene vacío
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
                    'slug' => \Illuminate\Support\Str::slug($name . '-' . \Illuminate\Support\Str::random(4)), 
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $blog->$relationName()->sync($ids);
    }
}
