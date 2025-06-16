<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //  Listar todos los empleados
    public function listarEmpleados()
    {
        $empleados = \App\Models\Empleado::with(['usuario', 'especialidades'])->get();
        return response()->json($empleados);
    }

    //  Registrar un nuevo empleado (ya lo tenías)
    public function registrarEmpleado(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|unique:users',
            'nombre' => 'required',
            'password' => 'required|confirmed',
            'correo' => 'required|email|unique:users',
            'telefono' => 'nullable',
            'rol' => 'required|in:empleado',
        ]);

        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario,
            'password' => Hash::make($request->password),
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'rol' => $request->rol,
        ]);

        return response()->json([
            'message' => 'Empleado registrado exitosamente',
            'user' => $user
        ], 201);
    }

    //  Actualizar datos del empleado
    public function actualizarEmpleado(Request $request, $id)
    {
        $empleado = \App\Models\Empleado::findOrFail($id);
        $user = $empleado->usuario;

        $request->validate([
            'nombre' => 'required|string',
            'nombre_usuario' => 'required|string|unique:users,nombre_usuario,' . $user->id,
            'correo' => 'required|email|unique:users,correo,' . $user->id,
            'telefono' => 'nullable',
            'fecha_contratacion' => 'nullable|date'
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
            'telefono' => $request->telefono
        ]);

        if ($request->has('fecha_contratacion')) {
            $empleado->update([
                'fecha_contratacion' => $request->fecha_contratacion
            ]);
        }

        return response()->json([
            'message' => 'Empleado actualizado exitosamente',
            'empleado' => $empleado->load('usuario')
        ]);
    }

    // Eliminar empleado
    public function eliminarEmpleado($id)
    {
        $user = User::whereIn('rol', ['empleado'])->findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Empleado eliminado correctamente'
        ]);
    }

    public function asignarEspecialidades(Request $request, $id)
    {
        $empleado = \App\Models\Empleado::findOrFail($id);

        $request->validate([
            'especialidades' => 'required|array',
            'especialidades.*' => 'exists:especialidades,id'
        ]);

        $empleado->especialidades()->sync($request->especialidades);

        return response()->json([
            'message' => 'Especialidades asignadas correctamente',
            'empleado' => $empleado->load('especialidades')
        ]);
    }
}
