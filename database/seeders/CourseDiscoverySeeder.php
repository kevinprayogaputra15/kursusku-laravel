<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseDiscoverySeeder extends Seeder
{
    /**
     * Data dummy untuk menguji search, filter, dan pagination.
     * - 30 course "DISC-001..030" (18 open, 6 closed, 6 draft)
     * - 1 course unik "UNIK-001" untuk skenario "1 hasil"
     * Aman dijalankan berulang (updateOrCreate pada code). Tidak mengubah schema.
     */
    public function run(): void
    {
        $topics = ['Web Modern', 'PHP Lanjutan', 'Laravel Praktis'];

        for ($i = 1; $i <= 30; $i++) {
            $status = match (true) {
                $i % 5 === 3 => 'closed',
                $i % 5 === 4 => 'draft',
                default => 'open',
            };

            Course::updateOrCreate(
                ['code' => sprintf('DISC-%03d', $i)],
                [
                    'name' => $topics[$i % 3].' '.sprintf('%02d', $i),
                    'description' => 'Data dummy pertemuan 13.',
                    'fee' => 200000 + ($i * 10000),
                    'quota' => 10 + $i,
                    'start_date' => now()->addDays(7 + $i)->toDateString(),
                    'status' => $status,
                ]
            );
        }

        Course::updateOrCreate(
            ['code' => 'UNIK-001'],
            [
                'name' => 'Kursus Unik Zeta',
                'description' => 'Satu-satunya data untuk skenario 1 hasil.',
                'fee' => 123000,
                'quota' => 5,
                'start_date' => now()->addDays(40)->toDateString(),
                'status' => 'open',
            ]
        );
    }
}
