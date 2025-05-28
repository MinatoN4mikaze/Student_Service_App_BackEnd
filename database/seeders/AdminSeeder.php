<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
        [
        'admin_id' => 1,
        'user_id' => 6,
        'position' => 'System Administrator',
        ],
        [
        'admin_id' => 2,
        'user_id' => 7,
        'position' => 'Academic Supervisor',
        ],
        [
        'admin_id' => 3,
        'user_id' => 8,
        'position' => 'Department Head',
        ]
    ];
    foreach($admins as $admin)
    {
        Admin::create($admin);
    }
    }
}
