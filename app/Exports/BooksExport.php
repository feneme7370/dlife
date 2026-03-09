<?php
namespace App\Exports;
use App\Models\Page\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BooksExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Book::with([
            'subjects',
            'collections',
            'genres',
            'tags',
            'reads'
        ])->get()->map(function ($book) {

            return [
                'title' => $book->title,
                'slug' => $book->slug,
                'original_title' => $book->original_title,
                'synopsis' => $book->synopsis,
                'release_date' => $book->release_date,
                'number_collection' => $book->number_collection,
                'pages' => $book->pages,
                'type' => $book->type,

                'summary' => $book->summary,
                'summary_clear' => $book->summary_clear,
                'notes' => $book->notes,
                'notes_clear' => $book->notes_clear,

                'is_favorite' => $book->is_favorite,
                'is_abandonated' => $book->is_abandonated,
                'is_public' => $book->is_public,
                'rating' => $book->rating,

                'cover_image' => $book->cover_image,
                'cover_image_url' => $book->cover_image_url,
                'uuid' => $book->uuid,
                'user_id' => $book->user_id,

                'subjects' => $book->subjects
                    ->pluck('name')
                    ->implode(', '),

                'collections' => $book->collections
                    ->pluck('name')
                    ->implode(', '),

                'genres' => $book->genres
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $book->tags
                    ->pluck('name')
                    ->implode(', '),

                'reads' => $book->reads
                    ->map(function ($read) {
                        return \Carbon\Carbon::parse($read->start_read)->format('Y-m-d') . ' → ' .
                            ($read->end_read ? \Carbon\Carbon::parse($read->end_read)->format('Y-m-d') : 'En progreso');
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
        'pages',
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
        'reads'
        ];
    }
}