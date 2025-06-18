<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Servicio;
use App\Models\AgendaEmpleado;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'especialidades' => 'required|array',
            'especialidades.*' => 'exists:especialidades,id',
            'empleado_id' => 'required|exists:empleados,id',
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($value < '08:00' || $value > '20:00') {
                        $fail('La hora debe estar entre 08:00 y 20:00.');
                    }
                }
            ],
        ]);

        // Obtener el usuario autenticado (asumiendo que está logueado)
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Buscar o crear el cliente basado en el usuario
        $cliente = Cliente::where('usuario_id', $user->id)->first();
        if (!$cliente) {
            $cliente = Cliente::create(['usuario_id' => $user->id]);
        }

        // Validar que la hora esté dentro de la agenda del barbero
        $agendas = AgendaEmpleado::where('empleado_id', $request->empleado_id)
            ->where('fecha', $request->fecha)
            ->where('disponible', true)
            ->get();
        $horaCita = Carbon::parse($request->hora);
        $agendaValida = false;
        foreach ($agendas as $agenda) {
            $inicio = Carbon::parse($agenda->hora_inicio);
            $fin = Carbon::parse($agenda->hora_fin);
            if ($horaCita >= $inicio && $horaCita < $fin) {
                $agendaValida = true;
                break;
            }
        }
        if (!$agendaValida) {
            return response()->json(['error' => 'La hora seleccionada no está dentro de la agenda del barbero para ese día.'], 422);
        }

        // Validar solapamiento de citas
        $servicio = Servicio::find($request->servicio_id);
        $duracion = $servicio->duracion_minutos ?? 30;
        $horaFinCita = $horaCita->copy()->addMinutes($duracion);
        $citasExistentes = Cita::where('empleado_id', $request->empleado_id)
            ->where('fecha', $request->fecha)
            ->whereIn('estado', ['pendiente', 'completada'])
            ->get();
        foreach ($citasExistentes as $citaExistente) {
            foreach ($citaExistente->servicios as $servicioExistente) {
                $horaInicioExistente = Carbon::parse($servicioExistente->pivot->hora);
                $horaFinExistente = $horaInicioExistente->copy()->addMinutes($servicioExistente->duracion_minutos ?? 30);
                // Si se solapan los intervalos
                if ($horaCita < $horaFinExistente && $horaFinCita > $horaInicioExistente) {
                    return response()->json(['error' => 'La hora seleccionada se cruza con otra cita existente para este barbero.'], 422);
                }
            }
        }

        // Crear la cita
        $cita = Cita::create([
            'cliente_id' => $cliente->id,
            'empleado_id' => $request->empleado_id,
            'fecha' => $request->fecha,
            'estado' => 'pendiente',
        ]);

        // Asociar el servicio con la hora específica
        $cita->servicios()->attach($request->servicio_id, ['hora' => $request->hora]);

        return response()->json([
            'message' => 'Cita creada exitosamente',
            'cita' => $cita->load(['servicios', 'cliente.usuario', 'empleado.usuario'])
        ], 201);
    }

    public function index()
    {
        return Cita::with(['servicios', 'cliente.usuario', 'empleado.usuario'])->get();
    }

    public function show($id)
    {
        $cita = Cita::with(['servicios', 'cliente.usuario', 'empleado.usuario'])->find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        return $cita;
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $user = $request->user();
        $isEmpleado = $user->rol === 'empleado';

        if ($isEmpleado) {
            // Empleado solo puede marcar como completada o reagendada
            if ($request->has('estado') && $request->estado === 'completada') {
                $cita->estado = 'completada';
            } elseif ($request->has('fecha') && $request->has('estado') && $request->estado === 'reagendada') {
                $cita->fecha = $request->fecha;
                $cita->estado = 'reagendada';
            } else {
                return response()->json(['error' => 'El empleado solo puede marcar la cita como completada o reagendada'], 403);
            }
        } else {
            // Otros roles pueden actualizar otros campos
            $cita->update($request->all());
        }

        $cita->save();
        return response()->json($cita);
    }
    

    public function destroy($id)
    {
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->servicios()->detach();
        $cita->delete();

        return response()->json(['message' => 'Cita eliminada correctamente']);
    }

    public function cancelar($id)
    {
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $cita->estado = 'cancelada';
        $cita->save();

        return response()->json(['message' => 'Cita cancelada correctamente']);
    }
}
