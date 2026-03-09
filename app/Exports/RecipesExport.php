<?php
namespace App\Exports;
use App\Models\Page\Recipe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecipesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Recipe::with([
            'categories',
            'tags',
        ])->get()->map(function ($recipe) {

            return [
                'title' => $recipe->title,
                'slug' => $recipe->slug,
                'description' => $recipe->description,

                'ingredients' => $recipe->ingredients,
                'ingredients_clear' => $recipe->ingredients_clear,
                'instructions' => $recipe->instructions,
                'instructions_clear' => $recipe->instructions_clear,

                'is_public' => $recipe->is_public,

                'cover_image' => $recipe->cover_image,
                'cover_image_url' => $recipe->cover_image_url,
                'uuid' => $recipe->uuid,
                'user_id' => $recipe->user_id,

                'categories' => $recipe->categories
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $recipe->tags
                    ->pluck('name')
                    ->implode(', '),
            ];
        });
    }

    public function headings(): array
    {
        return [
        'title',
        'slug',
        'description',

        'ingredients',
        'ingredients_clear',
        'instructions',
        'instructions_clear',

        'is_public',

        'cover_image',
        'cover_image_url',
        'uuid',
        'user_id',

        'categories',
        'tags',
        ];
    }
}