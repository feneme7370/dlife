<?php

namespace Database\Seeders\Page;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReadingFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formats = [
            'Fisico 📖',
            'Digital 💻',
            'Audiolibro 🎧',
        ];

        foreach ($formats as $format) {
            \App\Models\Page\ReadingFormat::updateOrCreate(
                ['name' => $format],
                [
                    'uuid' => \Illuminate\Support\Str::random(24),
                    'user_id' => 1,
                ]
            );
        }
    }
}
