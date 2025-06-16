<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class UpdateServiciosCategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            1 => 'Corte de Cabello',
            2 => 'Barba',
            3 => 'Combo',
            4 => 'Afeitado'
        ];

        foreach ($categorias as $id => $categoria) {
            $servicio = Servicio::find($id);
            if ($servicio) {
                $servicio->update(['categoria' => $categoria]);
            }
        }
    }
} 