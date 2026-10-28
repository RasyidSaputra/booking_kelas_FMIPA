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

        User::updateOrCreate(
            ['email' => 'ghalib@example.com'],
            [
                'name' => 'Sulthan Ghalib',
                'identity_number' => '2203110011',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ]
        );

        $lecturers = [
            [
                'name' => 'Dr. Ardiansyah, M.Si.',
                'email' => 'ardiansyah@fmipa.unri.ac.id',
                'identity_number' => '197805122003121002',
            ],
            [
                'name' => 'Prof. Dr. Siti Rahmah, M.Kom.',
                'email' => 'sitirahmah@fmipa.unri.ac.id',
                'identity_number' => '196904151994032001',
            ],
            [
                'name' => 'Bambang Hendrawan, M.Sc., Ph.D.',
                'email' => 'b.hendrawan@fmipa.unri.ac.id',
                'identity_number' => '198203102008121001',
            ],
            [
                'name' => 'Nurul Hidayati, S.Si., M.Mat.',
                'email' => 'nurul.h@fmipa.unri.ac.id',
                'identity_number' => '198809222015042002',
            ],
            [
                'name' => 'Dr. Eng. Rinaldi, M.T.',
                'email' => 'rinaldi@fmipa.unri.ac.id',
                'identity_number' => '197511082000031003',
            ],
            [
                'name' => 'Dewi Sartika, M.Si.',
                'email' => 'dewi.sartika@fmipa.unri.ac.id',
                'identity_number' => '198506142010122004',
            ],
        ];

        foreach ($lecturers as $lecturer) {
            User::updateOrCreate(
                ['email' => $lecturer['email']],
                [
                    'name' => $lecturer['name'],
                    'identity_number' => $lecturer['identity_number'],
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                ]
            );
        }
    }
}
