<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear al primer Jefe de Piso para poder iniciar sesión
        User::create([
            'name' => 'Jefe de Piso (Admin)',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('password123'), // Contraseña: password123
            'shift' => 'Mañana',
            'role' => 'supervisor',
        ]);
    }
}