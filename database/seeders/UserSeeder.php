<?php

namespace Database\Seeders;

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
        $users = [
        [
            'name' => 'Majd Ahmad',
            'email' => 'majd@gmail.com',
            'unique_id' => '12345678',
            'role' => 'student',
            'is_active' => 0,
        ],
        [
            'name' => 'Khaled Nour',
            'email' => 'khaled@gmail.com',
            'unique_id' => '87654321',
            'role' => 'student',
            'is_active' => 0,
        ],
        [
            'name' => 'Lina Salem',
            'email' => 'lina@gmail.com',
            'unique_id' => '11223344',
            'role' => 'student',
            'is_active' => 0,
        ],
        [
            'name' => 'Sara Hasan',
            'email' => 'sara@gmail.com',
            'unique_id' => '55667788',
            'role' => 'student',
            'is_active' => 0,
        ],
        [
            'name' => 'Omar Yassin',
            'email' => 'omar@gmail.com',
            'unique_id' => '99887766',
            'role' => 'student',
            'is_active' => 0,
        ],
        [
            'name' => 'Huda Ali',
            'email' => 'huda@gmail.com',
            'unique_id' => '33445566',
            'role' => 'admin',
            'is_active' => 0,
        ],
        [
            'name' => 'Tariq Fawaz',
            'email' => 'tariq@gmail.com',
            'unique_id' => '22110099',
            'role' => 'admin',
            'is_active' => 0,
        ],
        [
            'name' => 'Noor Ibrahim',
            'email' => 'noor@gmail.com',
            'unique_id' => '44556677',
            'role' => 'admin',
            'is_active' => 0,
        ]
    ];

    foreach($users as $user)
    {
        User::create($user);
    }

    }
}
