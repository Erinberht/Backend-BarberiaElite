<?php

// database/seeders/ClienteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\User;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $usuario1 = User::where('nombre_usuario', 'cliente1')->first();
        $usuario2 = User::where('nombre_usuario', 'cliente2')->first();
        $usuario3 = User::where('nombre_usuario', 'cliente3')->first();
        
        Cliente::create([
            'usuario_id' => $usuario1->id,
        ]);

        Cliente::create([
            'usuario_id' => $usuario2->id,
        ]);

        Cliente::create([
            'usuario_id' => $usuario3->id,
        ]);
    }
}

