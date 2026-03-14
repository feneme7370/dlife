<?php

namespace App\Exports;

use App\Models\Page\Blog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BlogsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Blog::with([
            'tags',
        ])->get()->map(function ($blog) {

            return [
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'type' => $blog->type,

                'content' => $blog->content,
                'content_clear' => $blog->content_clear,

                'is_public' => $blog->is_public,

                'cover_image' => $blog->cover_image,
                'cover_image_url' => $blog->cover_image_url,
                'uuid' => $blog->uuid,
                'user_id' => $blog->user_id,

                'tags' => $blog->tags
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
        'excerpt',
        'type',

        'content',
        'content_clear',

        'is_public',

        'cover_image',
        'cover_image_url',
        'uuid',
        'user_id',

        'tags',
        ];
    }
}
