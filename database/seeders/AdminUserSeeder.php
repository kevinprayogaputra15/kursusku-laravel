<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Akun admin untuk login. Password disimpan sebagai HASH (bcrypt)
     * melalui cast 'hashed' pada model User - bukan teks biasa.
     * Aman dijalankan berulang (updateOrCreate pada email).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kursusku.test'],
            [
                'name' => 'Admin KursusKu',
                'password' => 'Admin12345',
            ]
        );
    }
}
