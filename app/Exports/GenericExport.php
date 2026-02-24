<?php

namespace App\Exports;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromCollection, WithHeadings
{
    protected Collection $data;
    protected string $table;

    public function __construct(Collection $data, string $table)
    {
        $this->data = $data;
        $this->table = $table;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return Schema::getColumnListing($this->table);
    }
}