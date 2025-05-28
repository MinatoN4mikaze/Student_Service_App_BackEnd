<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
        [
        'student_id' => 'STU1001',
        'user_id' => 1,
        'year' => 1,
        'department' => 'Computer Science',
        ],
        [
        'student_id' => 'STU1002',
        'user_id' => 2,
        'year' => 2,
        'department' => 'Electrical Engineering',
        ],
        [
        'student_id' => 'STU1003',
        'user_id' => 3,
        'year' => 3,
        'department' => 'Mechanical Engineering',
        ],
        [
        'student_id' => 'STU1004',
        'user_id' => 4,
        'year' => 4,
        'department' => 'Mechanical Engineering',
        ],
        [
        'student_id' => 'STU1005',
        'user_id' => 5,
        'year' => 4,
        'department' => 'Mechanical Engineering',
        ],
        [
        'student_id' => 'STU1006',
        'user_id' => 6,
        'year' => 4,
        'department' => 'Mechanical Engineering',
        ],
    ];

    foreach($students as $student)
    {
        Student::create($student);
    }

    }
}
