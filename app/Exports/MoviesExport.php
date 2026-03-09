<?php
namespace App\Exports;
use App\Models\Page\Movie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MoviesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Movie::with([
            'subjects',
            'collections',
            'genres',
            'tags',
            'views'
        ])->get()->map(function ($movie) {

            return [
                'title' => $movie->title,
                'slug' => $movie->slug,
                'original_title' => $movie->original_title,
                'synopsis' => $movie->synopsis,
                'release_date' => $movie->release_date,

                'number_collection' => $movie->number_collection ?? 1,
                'runtime' => $movie->runtime ?? 1,
                'type' => $movie->type ?? 1,

                'summary' => $movie->summary,
                'summary_clear' => $movie->summary_clear,
                'notes' => $movie->notes,
                'notes_clear' => $movie->notes_clear,

                'is_favorite' => $movie->is_favorite,
                'is_abandonated' => $movie->is_abandonated,
                'is_public' => $movie->is_public,

                'rating' => $movie->rating ?? 0,

                'cover_image' => $movie->cover_image,
                'cover_image_url' => $movie->cover_image_url,
                'uuid' => $movie->uuid,
                'user_id' => $movie->user_id,

                'subjects' => $movie->subjects
                    ->pluck('name')
                    ->implode(', '),

                'collections' => $movie->collections
                    ->pluck('name')
                    ->implode(', '),

                'genres' => $movie->genres
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $movie->tags
                    ->pluck('name')
                    ->implode(', '),

                'views' => $movie->views
                    ->map(function ($view) {
                        return \Carbon\Carbon::parse($view->start_view)->format('Y-m-d') . ' → ' .
                            ($view->end_view ? \Carbon\Carbon::parse($view->end_view)->format('Y-m-d') : 'En progreso');
                    })
                    ->implode(' | ')
            ];
        });
    }

    public function headings(): array
    {
        return [
        'title',
        'slug',
        'original_title',
        'synopsis',
        'release_date',
        'number_collection',
        'runtime',
        'type',

        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',
        'is_public',
        'rating',

        'cover_image',
        'cover_image_url',
        'uuid',
        'user_id',

        'subjects',
        'collections',
        'genres',
        'tags',
        'views'
        ];
    }
}