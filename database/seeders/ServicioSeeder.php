<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        Servicio::create([
            'id' => 1,
            'nombre' => 'Corte de cabello',
            'precio' => 15000,
            'duracion_minutos' => 30,
        ]);

        Servicio::create([
            'id' => 2,
            'nombre' => 'Barba',
            'precio' => 10000,
            'duracion_minutos' => 20,
        ]);

        Servicio::create([
            'id' => 3,
            'nombre' => 'Corte + Barba',
            'precio' => 23000,
            'duracion_minutos' => 45,
        ]);
    }
}
