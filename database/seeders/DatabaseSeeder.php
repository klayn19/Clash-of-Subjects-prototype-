<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Admin Account ────────────────────────────────────────────
        if (!User::where('email', 'admin@cos.com')->exists()) {
            User::create([
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'age'        => 30,
                'email'      => 'admin@cos.com',
                'password'   => \Illuminate\Support\Facades\Hash::make('admin1234'),
                'role'       => 'admin',
            ]);
        }

        // ── Teacher Account ──────────────────────────────────────────
        if (!User::where('email', 'teacher@cos.com')->exists()) {
            User::create([
                'first_name' => 'Test',
                'last_name'  => 'Teacher',
                'age'        => 28,
                'email'      => 'teacher@cos.com',
                'password'   => \Illuminate\Support\Facades\Hash::make('teacher1234'),
                'role'       => 'teacher',
            ]);
        }
    }
}
