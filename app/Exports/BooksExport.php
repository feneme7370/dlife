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
            'book_subjects',
            'book_book_genres',
            'book_btags',
            'book_reads'
        ])->get()->map(function ($book) {

            return [
                'title' => $book->title,
                'slug' => $book->slug,
                'original_title' => $book->original_title,
                'synopsis' => $book->synopsis,
                'release_date' => $book->release_date,
                'number_collection' => $book->number_collection,
                'pages' => $book->pages,

                'summary' => $book->summary,
                'summary_clear' => $book->summary_clear,
                'notes' => $book->notes,
                'notes_clear' => $book->notes_clear,

                'is_favorite' => $book->is_favorite,
                'is_abandonated' => $book->is_abandonated,
                'rating' => $book->rating,

                'cover_image' => $book->cover_image,
                'cover_image_url' => $book->cover_image_url,
                'uuid' => $book->uuid,
                'user_id' => $book->user_id,

                'subjects' => $book->book_subjects
                    ->pluck('name')
                    ->implode(', '),

                'collections' => $book->book_collections
                    ->pluck('name')
                    ->implode(', '),

                'genres' => $book->book_book_genres
                    ->pluck('name')
                    ->implode(', '),

                'tags' => $book->book_btags
                    ->pluck('name')
                    ->implode(', '),

                'reads' => $book->book_reads
                    ->map(function ($read) {
                        return $read->start_read . ' → ' .
                            ($read->end_read ?? 'En progreso');
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

        'summary',
        'summary_clear',
        'notes',
        'notes_clear',
        
        'is_favorite',
        'is_abandonated',
        'rating',

        'cover_image',
        'cover_image_url',
        'uuid',
        'user_id',

        'book_subjects',
        'book_collections',
        'book_genres',
        'book_tags',
        'book_reads'
        ];
    }
}