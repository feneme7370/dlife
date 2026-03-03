<?php

namespace App\Exports;

use App\Models\Page\Diary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyLogExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Diary::with([
                    'diary_dtags',
                    'diary_dcategories',
                ])->get()->map(function ($item) {

                    return [
                        'title' => $item->title,
                        'day' => $item->day,
                        'status' => $item->status,
                        'content' => $item->content,
                        'content_clear' => $item->content_clear,
                        'uuid' => $item->uuid,
                        'user_id' => $item->user_id,

                        'categories' => $item->diary_dcategories
                            ->pluck('name')
                            ->implode(', '),

                        'tags' => $item->diary_dtags
                            ->pluck('name')
                            ->implode(', '),
                    ];
                });
    }

public function headings(): array
    {
        return [
        'title',
        'day',
        'status',
        'content',
        'content_clear',
        'uuid',
        'user_id',

        'categories',
        'tags',
        ];
    }
}
