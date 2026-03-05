<?php

namespace Database\Seeders\Page;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            'Español 🇪🇸',
            'Ingles 🇬🇧',
            'Italiano 🇮🇹',
            'Chino 🇨🇳',
            'Frances 🇫🇷',
            'Aleman 🇩🇪',
            'Portugues 🇵🇹',
            'Japones 🇯🇵',
            'Otros 🌍',
        ];
        foreach ($languages as $language) {
            \App\Models\Page\Language::updateOrCreate(
                ['name' => $language],
                [
                    'uuid' => \Illuminate\Support\Str::random(24),
                    'user_id' => 1,
                ]
            );
        }
    }
}
