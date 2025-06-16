<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'id' => 1,
            'nombre_usuario' => 'cliente1',
            'password' => Hash::make('password123'),
            'rol' => 'cliente',
            'nombre' => 'Juan Pérez',
            'correo' => 'juan@example.com',
            'telefono' => '3001234567',
        ]);

        User::create([
            'id' => 2,
            'nombre_usuario' => 'cliente2',
            'password' => Hash::make('password456'),
            'rol' => 'cliente',
            'nombre' => 'María Gómez',
            'correo' => 'maria@example.com',
            'telefono' => '3107654321',
        ]);

        User::create([
            'id' => 3,
            'nombre_usuario' => 'cliente3',
            'password' => Hash::make('password789'),
            'rol' => 'cliente',
            'nombre' => 'Carlos López',
            'correo' => 'carlos@example.com',
            'telefono' => '3178901234',
        ]);
    }
}

