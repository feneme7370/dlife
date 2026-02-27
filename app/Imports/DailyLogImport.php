<?php

namespace App\Imports;

use App\Models\DailyLog;
use App\Models\Page\Diary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DailyLogImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Validar que tenga uuid
        if (empty($row['uuid'])) {
            return null;
        }

        // Parsear fecha correctamente (soporta número Excel o texto)
        $day = $this->parseDate($row['day'] ?? null);
        
        $day = $day?->format('Y-m-d');
        
        return Diary::updateOrCreate(
            [
                'uuid' => $row['uuid'],
                'user_id' => Auth::id(),
            ],
            [
                'day' => $day,
                'status' => $row['status'] ?? 0,
                'title' => $row['title'] ?? null,
                'content' => $row['content'] ?? null,
            ]
        );
    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject($value)
                );
            }

            return Carbon::parse($value);

        } catch (\Exception $e) {
            return null;
        }
    }
}