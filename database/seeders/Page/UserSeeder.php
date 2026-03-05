<?php

namespace Database\Seeders\Page;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'feneme',
            'email' => 'marascofederico95@gmail.com',
            'password' => '$2y$12$dWGDEaTHB.Qb5/peaxg0ZO52wYmtP4cGVMAoId2Ege2ywtqTKYm0m',
        ]);
    }
}
