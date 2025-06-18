<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\AgendaEmpleado;
use App\Models\Cita;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    public function index()
    {
        return Empleado::with(['usuario', 'especialidades'])->get();
    }

    public function show($id)
    {
        $empleado = Empleado::with(['usuario', 'especialidades'])->find($id);
        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
        return $empleado;
    }

    public function getHorariosDisponibles($id, Request $request)
    {
        try {
            Log::info('Iniciando getHorariosDisponibles', [
                'empleado_id' => $id,
                'fecha' => $request->fecha
            ]);

            $request->validate([
                'fecha' => 'required|date|after_or_equal:today'
            ]);

            $empleado = Empleado::findOrFail($id);
            $fecha = Carbon::parse($request->fecha);

            Log::info('Buscando agendas para el empleado', [
                'empleado_id' => $id,
                'fecha' => $fecha->format('Y-m-d')
            ]);

            // Obtener todas las agendas disponibles para la fecha
            $agendas = AgendaEmpleado::where('empleado_id', $id)
                ->where('fecha', $fecha->format('Y-m-d'))
                ->where('disponible', true)
                ->get();

            Log::info('Agendas encontradas', [
                'count' => $agendas->count(),
                'agendas' => $agendas->toArray()
            ]);

            if ($agendas->isEmpty()) {
                return response()->json([
                    'message' => 'El empleado no tiene horarios disponibles para la fecha seleccionada',
                    'horarios' => []
                ]);
            }

            $horarios = [];
            
            // Procesar cada bloque de horario
            foreach ($agendas as $agenda) {
                try {
                    Log::info('Procesando agenda', [
                        'agenda_id' => $agenda->id,
                        'hora_inicio' => $agenda->hora_inicio,
                        'hora_fin' => $agenda->hora_fin
                    ]);

                    $horaInicio = $agenda->hora_inicio;
                    $horaFin = $agenda->hora_fin;

                    if (!$horaInicio || !$horaFin) {
                        Log::warning('Horarios inválidos en agenda', [
                            'agenda_id' => $agenda->id,
                            'hora_inicio' => $horaInicio,
                            'hora_fin' => $horaFin
                        ]);
                        continue;
                    }

                    // Construir la fecha y hora correctamente
                    $horaActual = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horaInicio);
                    $horaFinCarbon = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horaFin);

                    if (!$horaActual || !$horaFinCarbon) {
                        Log::warning('Error al parsear horarios', [
                            'agenda_id' => $agenda->id,
                            'hora_inicio' => $horaInicio,
                            'hora_fin' => $horaFin
                        ]);
                        continue;
                    }

                    while ($horaActual < $horaFinCarbon) {
                        // Si la fecha es hoy, solo mostrar horarios futuros
                        if ($fecha->isToday()) {
                            $ahora = Carbon::now();
                            if ($horaActual > $ahora->addMinutes(60)) { // Al menos 1 hora de anticipación
                                $horarios[] = $horaActual->format('H:i');
                            }
                        } else {
                            $horarios[] = $horaActual->format('H:i');
                        }
                        $horaActual->addMinutes(30);
                    }
                } catch (\Exception $e) {
                    Log::error('Error procesando agenda individual', [
                        'agenda_id' => $agenda->id,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Eliminar duplicados y ordenar
            $horarios = array_unique($horarios);
            sort($horarios);

            Log::info('Horarios generados', [
                'count' => count($horarios),
                'horarios' => $horarios
            ]);

            // Obtener citas ya reservadas para filtrarlas
            $citasReservadas = Cita::where('empleado_id', $id)
                ->where('fecha', $fecha->format('Y-m-d'))
                ->whereIn('estado', ['pendiente', 'completada'])
                ->join('cita_servicio', 'citas.id', '=', 'cita_servicio.cita_id')
                ->pluck('cita_servicio.hora')
                ->toArray();

            Log::info('Citas reservadas', [
                'count' => count($citasReservadas),
                'citas' => $citasReservadas
            ]);

            // Filtrar horarios ya reservados
            $horariosDisponibles = array_diff($horarios, $citasReservadas);

            // Obtener información de la primera agenda para mostrar
            $primeraAgenda = $agendas->first();

            $response = [
                'horarios' => array_values($horariosDisponibles),
                'agenda_info' => [
                    'fecha' => $primeraAgenda->fecha instanceof \Carbon\Carbon
                        ? $primeraAgenda->fecha->format('Y-m-d')
                        : (is_string($primeraAgenda->fecha)
                            ? substr($primeraAgenda->fecha, 0, 10)
                            : ''),
                    'hora_inicio' => $primeraAgenda->hora_inicio,
                    'hora_fin' => $primeraAgenda->hora_fin,
                    'empleado' => $empleado->usuario->nombre
                ]
            ];

            Log::info('Respuesta final', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error en getHorariosDisponibles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'empleado_id' => $id,
                'fecha' => $request->fecha ?? null
            ]);
            
            return response()->json([
                'message' => 'Error al obtener los horarios disponibles: ' . $e->getMessage(),
                'horarios' => []
            ], 500);
        }
    }

    public function getFechasDisponibles($id, Request $request)
    {
        $hoy = Carbon::today();
        $agendas = AgendaEmpleado::where('empleado_id', $id)
            ->where('fecha', '>=', $hoy)
            ->where('disponible', true)
            ->orderBy('fecha')
            ->get();
        $fechasDisponibles = [];
        foreach ($agendas as $agenda) {
            $fecha = $agenda->fecha->format('Y-m-d');
            $horaInicio = Carbon::parse($agenda->hora_inicio);
            $horaFin = Carbon::parse($agenda->hora_fin);
            $horas = [];
            $horaActual = $horaInicio->copy();
            while ($horaActual->copy()->addMinutes(30) <= $horaFin) {
                $horas[] = $horaActual->format('H:i');
                $horaActual->addMinutes(30);
            }
            // Obtener citas ya reservadas para ese día
            $citasReservadas = Cita::where('empleado_id', $id)
                ->where('fecha', $fecha)
                ->whereIn('estado', ['pendiente', 'completada'])
                ->join('cita_servicio', 'citas.id', '=', 'cita_servicio.cita_id')
                ->pluck('cita_servicio.hora')
                ->toArray();
            $horasDisponibles = array_diff($horas, $citasReservadas);
            if (count($horasDisponibles) > 0) {
                $fechasDisponibles[] = $fecha;
            }
        }
        $fechasDisponibles = array_unique($fechasDisponibles);
        return response()->json(['fechas' => array_values($fechasDisponibles)]);
    }

    public function misCitas(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $empleado = Empleado::where('usuario_id', $user->id)->first();
            if (!$empleado) {
                return response()->json(['error' => 'No se encontró el empleado'], 404);
            }

            $citas = Cita::where('empleado_id', $empleado->id)
                ->with(['servicios', 'cliente.usuario'])
                ->orderBy('fecha', 'desc')
                ->get();

            return response()->json($citas);
        } catch (\Exception $e) {
            Log::error('Error en misCitas del empleado', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);
            
            return response()->json([
                'error' => 'Error al obtener las citas'
            ], 500);
        }
    }
} 