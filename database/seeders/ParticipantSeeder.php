<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * 3 participant dummy (domain example.test = bukan email nyata).
     * Aman dijalankan berulang karena memakai updateOrCreate pada email.
     */
    public function run(): void
    {
        $participants = [
            ['Alya Rahma', 'alya@example.test', '081200000001'],
            ['Bima Putra', 'bima@example.test', '081200000002'],
            ['Citra Nabila', 'citra@example.test', null],
        ];

        foreach ($participants as [$name, $email, $phone]) {
            Participant::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone],
            );
        }
    }
}
