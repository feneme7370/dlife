<?php

namespace App\Imports;

use App\Models\Page\Diary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DiaryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {      
        $diary = Diary::updateOrCreate(
            [
                'uuid' => $row['uuid'],
            ],
            [
                'day' => Date::excelToDateTimeObject($row['day']) ?? null,
                'title' => $row['title'] ?? null,
                'status' => $row['status'] ?? 0,
                'content' => $row['content'] ?? null,
                'content_clear' => $row['content_clear'] ?? null,
                'user_id' => $row['user_id'] ?? Auth::id(),
                'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
            ]
        );
        // 2️⃣ Sync relaciones many-to-many
        $this->syncRelation($diary, $row['categories'], \App\Models\Page\Category::class, 'categories');
        $this->syncRelation($diary, $row['tags'], \App\Models\Page\Tag::class, 'tags');

        return $diary;
    }


    private function syncRelation($diary, $column, $modelClass, $relationName)
    {
        if (empty($column)) {
            $diary->$relationName()->sync([]); // limpia si viene vacío
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
                    'tag_type' => 'diaries',
                    'category_type' => 'diaries',
                    'uuid' => \Illuminate\Support\Str::random(24), 
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $ids[] = $model->id;
        }

        $diary->$relationName()->sync($ids);
    }
}