<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['WEB-101', 'Web Dasar', 300000, 25, 'open'],
            ['PHP-101', 'PHP Fundamental', 400000, 25, 'open'],
            ['LRV-101', 'Laravel Dasar', 500000, 20, 'open'],
            ['SQL-101', 'MySQL Dasar', 350000, 30, 'open'],
            ['UI-101', 'Bootstrap UI', 300000, 25, 'draft'],
            ['JS-101', 'JavaScript Dasar', 400000, 20, 'open'],
            ['AJAX-101', 'jQuery dan Ajax', 450000, 20, 'draft'],
            ['API-101', 'REST API Dasar', 550000, 18, 'closed'],
        ];

        foreach ($courses as $i => $item) {
            Course::create([
                'code' => $item[0],
                'name' => $item[1],
                'description' => 'Data latihan KursusKu.',
                'fee' => $item[2],
                'quota' => $item[3],
                'start_date' => now()->addDays(7 + ($i * 7))->toDateString(),
                'status' => $item[4],
            ]);
        }
    }
}
