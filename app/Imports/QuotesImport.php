<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuotesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Crear o actualizar por UUID
        $quote = \App\Models\Page\Quote::updateOrCreate(
            ['uuid' => $row['uuid']], // clave única
            [
                'content' => $row['content'],
                'author' => $row['author'],
                'source' => $row['source'],
                'user_id' => $row['user_id'] ?? Auth::id(),
                'uuid' => $row['uuid'] ?? \Illuminate\Support\Str::random(24),
            ]
        );

        return $quote;
    }
}