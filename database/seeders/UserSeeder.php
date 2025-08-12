<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear usuario administrador si no existe
        User::firstOrCreate(
            ['email' => 'admin@imss.gob.mx'],
            [
                'name' => 'Administrador IMSS-Bienestar',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario normal si no existe
        User::firstOrCreate(
            ['email' => 'usuario@imss.gob.mx'],
            [
                'name' => 'Usuario Sistema',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
