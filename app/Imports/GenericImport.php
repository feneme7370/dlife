<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GenericImport implements ToCollection, WithHeadingRow
{
    protected string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $data = $row->toArray();

            unset($data['id'], $data['created_at'], $data['updated_at']);

            $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table($this->table)->updateOrInsert(
                ['uuid' => $data['uuid']],
                $data
            );
        }
    }
}
