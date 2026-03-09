<?php
namespace App\Exports;
use App\Models\Page\Serie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SeriesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Serie::with([
            'subjects',
            'collections',
            'genres',
            'tags',
            'views'
        ])->get()->map(function ($serie) {

            return [
                'title' => $serie->title,
                'slug' => $serie->slug,
                'original_title' => $serie->original_title,
                'synopsis' => $serie->synopsis,
                'start_date' => $serie->start_date,
                'end_date' => $serie->end_date,

                'number_collection' => $serie->number_collection ?? 1,
                'seasons' => $serie->seasons ?? 0,
                'episodes' => $serie->episodes ?? 0,
                'type' => $serie->type ?? 1,

                'summary' => $serie->summary,
                'summary_clear' => $serie->summary_clear,
                'notes' => $serie->notes,
                'notes_clear' => $serie->notes_clear,

                'is_favorite' => $serie->is_favorite,
                'is_abandonated' => $serie->is_abandonated,
                'is_public' => $serie->is_public,

                'rating' => $serie->rating ?? 0,

                'cover_image' => $serie->cover_image,
                'cover_image_url' => $serie->cover_image_url,
                'uuid' => $serie->uuid,
                'user_id' => $serie->user_id,

                'subjects' => $serie->subjects
                    ->pluck('name')
                    ->implode(', '),

                'collections' => $serie->collections
                    ->pluck('name')
                    ->implode(', '),

                'genres' => $serie->genres
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $serie->tags
                    ->pluck('name')
                    ->implode(', '),

                'views' => $serie->views
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
        'start_date',
        'end_date',
        'number_collection',
        'seasons',
        'episodes',
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