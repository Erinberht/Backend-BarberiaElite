<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function index()
    {
        return response()->json(Servicio::with('especialidades')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:1',
            'categoria' => 'required|string|max:50',
        ]);

        $servicio = Servicio::create($request->all());

        return response()->json($servicio, 201);
    }

    // Mostrar un servicio por su ID
    public function show($id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio);
    }

    // Actualizar un servicio
    public function update(Request $request, $id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'precio' => 'sometimes|required|numeric|min:0',
            'duracion_minutos' => 'sometimes|required|integer|min:1',
            'categoria' => 'sometimes|required|string|max:50',
        ]);

        $servicio->update($request->only(['nombre', 'precio', 'duracion_minutos', 'categoria']));

        return response()->json($servicio);
    }

    // Eliminar un servicio
    public function destroy($id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }

    public function asignarEspecialidades(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $request->validate([
            'especialidades' => 'required|array',
            'especialidades.*' => 'exists:especialidades,id'
        ]);

        $servicio->especialidades()->sync($request->especialidades);

        return response()->json([
            'message' => 'Especialidades asignadas correctamente',
            'servicio' => $servicio->load('especialidades')
        ]);
    }
}
