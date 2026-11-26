<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun admin awal. Password ditulis polos di sini, lalu otomatis di-hash
     * oleh cast 'hashed' pada model User (tersimpan sebagai hash di database).
     * Aman dijalankan berulang (updateOrCreate pada username).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin KursusKu',
                'email' => 'admin@kursusku.test',
                'password' => 'admin12345',
                'role' => 'admin',
            ],
        );
    }
}
