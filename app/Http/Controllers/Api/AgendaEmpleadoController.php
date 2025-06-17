<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgendaEmpleado;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AgendaEmpleadoController extends Controller
{
    public function misHorarios(Request $request)
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

            $horarios = AgendaEmpleado::where('empleado_id', $empleado->id)
                ->where('fecha', '>=', Carbon::today())
                ->orderBy('fecha', 'asc')
                ->orderBy('hora_inicio', 'asc')
                ->get();

            return response()->json($horarios);
        } catch (\Exception $e) {
            Log::error('Error en misHorarios', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null
            ]);
            
            return response()->json([
                'error' => 'Error al obtener los horarios: ' . $e->getMessage()
            ], 500);
        }
    }

    public function guardarHorario(Request $request)
    {
        try {
            $request->validate([
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio'
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $empleado = Empleado::where('usuario_id', $user->id)->first();
            if (!$empleado) {
                return response()->json(['error' => 'No se encontró el empleado'], 404);
            }

            DB::beginTransaction();

            try {
                // Verificar si ya existe un horario que se solape
                $horarioExistente = AgendaEmpleado::where('empleado_id', $empleado->id)
                    ->where('fecha', $request->fecha)
                    ->where(function ($query) use ($request) {
                        $query->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                            ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin])
                            ->orWhere(function ($q) use ($request) {
                                $q->where('hora_inicio', '<=', $request->hora_inicio)
                                    ->where('hora_fin', '>=', $request->hora_fin);
                            });
                    })
                    ->first();

                if ($horarioExistente) {
                    return response()->json([
                        'error' => 'Ya existe un horario que se solapa con el horario seleccionado'
                    ], 422);
                }

                $horario = AgendaEmpleado::create([
                    'empleado_id' => $empleado->id,
                    'fecha' => $request->fecha,
                    'hora_inicio' => $request->hora_inicio,
                    'hora_fin' => $request->hora_fin,
                    'disponible' => true
                ]);

                DB::commit();
                return response()->json($horario, 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error en guardarHorario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Error al guardar el horario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarHorario(Request $request, $id)
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

            $horario = AgendaEmpleado::where('id', $id)
                ->where('empleado_id', $empleado->id)
                ->first();

            if (!$horario) {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }

            $horario->delete();

            return response()->json(['message' => 'Horario eliminado correctamente']);
        } catch (\Exception $e) {
            Log::error('Error en eliminarHorario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
                'horario_id' => $id
            ]);
            
            return response()->json([
                'error' => 'Error al eliminar el horario: ' . $e->getMessage()
            ], 500);
        }
    }
} 