<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Clean start for the real/final demo: ONE admin account and nothing else.
 *   php artisan migrate:fresh --seeder=AdminOnlySeeder
 * Log in with admin@mubs.ac.ug / ChangeMe#2026, then add doctors and schedules from the admin pages.
 * (This erases ALL data, including the demo data.)
 */
class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'System Admin',
            'email'    => 'admin@mubs.ac.ug',
            'password' => Hash::make('ChangeMe#2026'),
            'role'     => 'admin',
        ]);
    }
}
