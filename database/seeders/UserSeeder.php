<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fmipa.ac.id'],
            [
                'name' => 'Administrator FMIPA',
                'identity_number' => '198501012010121001',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
    }
}
