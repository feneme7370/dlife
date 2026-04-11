<?php
namespace App\Exports;
use App\Models\Page\Game;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GamesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Game::with([
            'subjects',
            'collections',
            'categories',
            'tags',
            'platforms',
            'playeds'
        ])->get()->map(function ($game) {

            return [
                'title' => $game->title,
                'slug' => $game->slug,
                'original_title' => $game->original_title,
                'synopsis' => $game->synopsis,
                'release_date' => $game->release_date,
                
                'summary' => $game->summary,
                'summary_clear' => $game->summary_clear,
                'notes' => $game->notes,
                'notes_clear' => $game->notes_clear,
                
                'is_favorite' => $game->is_favorite,
                'is_abandonated' => $game->is_abandonated,
                'is_public' => $game->is_public,
                
                'rating' => $game->rating ?? 0,
                'type' => $game->type ?? 1,

                'cover_image' => $game->cover_image,
                'cover_image_url' => $game->cover_image_url,
                'uuid' => $game->uuid,
                'user_id' => $game->user_id,

                'subjects' => $game->subjects
                    ->pluck('name')
                    ->implode(', '),

                'collections' => $game->collections
                    ->pluck('name')
                    ->implode(', '),

                'categories' => $game->categories
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $game->tags
                    ->pluck('name')
                    ->implode(', '),

                'platforms' => $game->platforms
                    ->pluck('name')
                    ->implode(', '),

                'playeds' => $game->playeds
                    ->map(function ($played) {
                        return \Carbon\Carbon::parse($played->start_played)->format('Y-m-d') . ' → ' .
                            ($played->end_played ? \Carbon\Carbon::parse($played->end_played)->format('Y-m-d') : 'En progreso');
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
        
        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',
        'is_public',
        
        'rating',
        'type',

        'cover_image',
        'cover_image_url',
        'uuid',
        'user_id',

        'subjects',
        'collections',
        'categories',
        'tags',
        'platforms',
        'playeds'
        ];
    }
}