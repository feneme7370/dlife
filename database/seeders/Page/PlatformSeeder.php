<?php

namespace Database\Seeders\Page;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
   $platforms = [

            // 🖥️ PC
            ['name' => 'PC', 'brand' => 'PC', 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🎮 PlayStation
            ['name' => 'PlayStation', 'brand' => 'Sony', 'release_year' => 1994, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation 2', 'brand' => 'Sony', 'release_year' => 2000, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation 3', 'brand' => 'Sony', 'release_year' => 2006, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation 4', 'brand' => 'Sony', 'release_year' => 2013, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation 5', 'brand' => 'Sony', 'release_year' => 2020, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation Portable (PSP)', 'brand' => 'Sony', 'release_year' => 2004, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'PlayStation Vita', 'brand' => 'Sony', 'release_year' => 2011, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🎮 Xbox
            ['name' => 'Xbox', 'brand' => 'Microsoft', 'release_year' => 2001, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Xbox 360', 'brand' => 'Microsoft', 'release_year' => 2005, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Xbox One', 'brand' => 'Microsoft', 'release_year' => 2013, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Xbox Series X/S', 'brand' => 'Microsoft', 'release_year' => 2020, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🎮 Nintendo
            ['name' => 'NES', 'brand' => 'Nintendo', 'release_year' => 1983, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'SNES', 'brand' => 'Nintendo', 'release_year' => 1990, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Nintendo 64', 'brand' => 'Nintendo', 'release_year' => 1996, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'GameCube', 'brand' => 'Nintendo', 'release_year' => 2001, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Wii', 'brand' => 'Nintendo', 'release_year' => 2006, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Wii U', 'brand' => 'Nintendo', 'release_year' => 2012, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Nintendo Switch', 'brand' => 'Nintendo', 'release_year' => 2017, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Game Boy', 'brand' => 'Nintendo', 'release_year' => 1989, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Game Boy Color', 'brand' => 'Nintendo', 'release_year' => 1998, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Game Boy Advance', 'brand' => 'Nintendo', 'release_year' => 2001, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Nintendo DS', 'brand' => 'Nintendo', 'release_year' => 2004, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Nintendo 3DS', 'brand' => 'Nintendo', 'release_year' => 2011, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🔵 SEGA
            ['name' => 'Sega Master System', 'brand' => 'Sega', 'release_year' => 1985, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Sega Genesis / Mega Drive', 'brand' => 'Sega', 'release_year' => 1988, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Sega CD', 'brand' => 'Sega', 'release_year' => 1991, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Sega 32X', 'brand' => 'Sega', 'release_year' => 1994, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Sega Saturn', 'brand' => 'Sega', 'release_year' => 1994, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Sega Dreamcast', 'brand' => 'Sega', 'release_year' => 1998, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Game Gear', 'brand' => 'Sega', 'release_year' => 1990, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🟤 Atari
            ['name' => 'Atari 2600', 'brand' => 'Atari', 'release_year' => 1977, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Atari 5200', 'brand' => 'Atari', 'release_year' => 1982, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Atari 7800', 'brand' => 'Atari', 'release_year' => 1986, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Atari Jaguar', 'brand' => 'Atari', 'release_year' => 1993, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Atari Lynx', 'brand' => 'Atari', 'release_year' => 1989, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 🟡 Otros retro
            ['name' => 'Neo Geo', 'brand' => 'SNK', 'release_year' => 1990, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Neo Geo CD', 'brand' => 'SNK', 'release_year' => 1994, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'TurboGrafx-16 / PC Engine', 'brand' => 'NEC', 'release_year' => 1987, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Commodore 64', 'brand' => 'Commodore', 'release_year' => 1982, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'MSX', 'brand' => 'Microsoft', 'release_year' => 1983, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'ZX Spectrum', 'brand' => 'Sinclair', 'release_year' => 1982, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // 📱 Mobile
            ['name' => 'Android', 'brand' => 'Google', 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'iOS', 'brand' => 'Apple', 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

            // ☁️ Otros
            ['name' => 'Steam Deck', 'brand' => 'Valve', 'release_year' => 2022, 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],
            ['name' => 'Cloud Gaming', 'brand' => 'Various', 'uuid' => \Illuminate\Support\Str::random(24), 'user_id' => 1],

        ];

        foreach ($platforms as $platform) {
            \App\Models\Page\Platform::create($platform);
        }
    }
}
