<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AgendaEmpleado;
use App\Models\Empleado;
use Carbon\Carbon;

class AgendaEmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los empleados
        $empleados = Empleado::all();
        
        // Crear agenda para los próximos 7 días
        $fechaInicio = Carbon::today();
        $fechaFin = Carbon::today()->addDays(7);
        
        foreach ($empleados as $empleado) {
            $fechaActual = $fechaInicio->copy();
            
            while ($fechaActual->lte($fechaFin)) {
                // Crear horarios de lunes a viernes
                if ($fechaActual->isWeekday()) {
                    AgendaEmpleado::create([
                        'empleado_id' => $empleado->id,
                        'fecha' => $fechaActual->format('Y-m-d'),
                        'hora_inicio' => '08:00:00',
                        'hora_fin' => '17:00:00',
                        'disponible' => true
                    ]);
                }
                
                // Crear horarios de sábado (medio día)
                if ($fechaActual->isSaturday()) {
                    AgendaEmpleado::create([
                        'empleado_id' => $empleado->id,
                        'fecha' => $fechaActual->format('Y-m-d'),
                        'hora_inicio' => '08:00:00',
                        'hora_fin' => '13:00:00',
                        'disponible' => true
                    ]);
                }
                
                $fechaActual->addDay();
            }
        }
        
        $this->command->info('Agenda de empleados creada para los próximos 7 días.');
    }
}
