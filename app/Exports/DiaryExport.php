<?php

namespace App\Exports;

use App\Models\Page\Diary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DiaryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Diary::with([
                    'tags',
                    'categories',
                ])->get()->map(function ($item) {

                    return [
                        'day' => Date::dateTimeToExcel($item->day),
                        'title' => $item->title,
                        'status' => $item->status,
                        'content' => $item->content,
                        'content_clear' => $item->content_clear,
                        'uuid' => $item->uuid,
                        'user_id' => $item->user_id,

                        'categories' => $item->categories
                            ->pluck('name')
                            ->implode(', '),

                        'tags' => $item->tags
                            ->pluck('name')
                            ->implode(', '),
                    ];
                });
    }

public function headings(): array
    {
        return [
        'day',
        'title',
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
