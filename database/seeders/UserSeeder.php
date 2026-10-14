<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Correo oficial corregido
        User::updateOrCreate(
            ['email' => 'DonLudo@gmail.com'],
            [
                'name' => 'Don Ludo',
                'password' => Hash::make('tiochu123'),
            ]
        );

        // Alias por si se escribe con terminación .chu
        User::updateOrCreate(
            ['email' => 'DonLudo@gmail.chu'],
            [
                'name' => 'Don Ludo',
                'password' => Hash::make('tiochu123'),
            ]
        );
    }
}
