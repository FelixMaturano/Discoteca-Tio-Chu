<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            StaffSeeder::class,
            NightSessionSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'DonLudo@gmail.chu'],
            [
                'name' => 'Don Ludo',
                'password' => Hash::make(config('app.admin_password') ?: 'tiochu123'),
                'role' => 'admin',
            ]
        );
    }
}
