<?php

namespace App\Http\Controllers\Api;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Especialidad::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
            ]);

            $especialidad = Especialidad::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            return response()->json(['message' => 'Especialidad creada', 'data' => $especialidad], 201);
        } catch (\Throwable $e) {
            Log::error('Error al crear especialidad: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear especialidad: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Especialidad::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);
        $especialidad->update($request->only(['nombre', 'descripcion']));
        return response()->json($especialidad);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $especialidad->delete();
        return response()->json(['message' => 'Especialidad eliminada']);
    }
}
