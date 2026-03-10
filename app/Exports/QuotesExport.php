<?php

namespace App\Exports;

use App\Models\Page\Quote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuotesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Quote::get()->map(function ($item) {

                    return [
                        'content' => $item->content,
                        'author' => $item->author,
                        'source' => $item->source,
                        'uuid' => $item->uuid,
                        'user_id' => $item->user_id,
                    ];
                });
    }

public function headings(): array
    {
        return [
        'content',
        'author',
        'source',
        'uuid',
        'user_id',
        ];
    }
}
