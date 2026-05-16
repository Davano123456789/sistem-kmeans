<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'koordinator@gmail.com'],
            [
                'nama' => 'Koordinator Skripsi',
                'password' => Hash::make('password123'),
                'role' => 'koordinator skripsi',
            ]
        );
    }
}
