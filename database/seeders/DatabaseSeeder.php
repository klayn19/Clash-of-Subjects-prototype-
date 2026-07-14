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
        // Create default Admin
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'age'        => 30, // just a default
                'email'      => 'admin@gmail.com',
                'password'   => \Illuminate\Support\Facades\Hash::make('password'),
                'role'       => 'admin',
            ]);
        }
    }
}
