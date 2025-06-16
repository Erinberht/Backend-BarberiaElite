<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function index()
    {
        return Cliente::with('usuario')->get();
    }

    public function show($id)
    {
        $cliente = Cliente::with('usuario')->find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
        return $cliente;
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'telefono' => 'required|string',
        ]);

        $cliente = Cliente::create($request->all());

        return response()->json($cliente->load('usuario'), 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $request->validate([
            'telefono' => 'sometimes|required|string',
        ]);

        $cliente->update($request->only('telefono'));

        return response()->json($cliente->load('usuario'));
    }

    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }

    public function misCitas()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cliente = Cliente::where('usuario_id', $user->id)->first();
        Log::info('Cliente encontrado:', ['user_id' => $user->id, 'cliente' => $cliente]);
        if (!$cliente) {
            Log::info('No se encontró cliente para el usuario.');
            return response()->json([]);
        }

        $citas = Cita::where('cliente_id', $cliente->id)
            ->with(['servicios', 'empleado.usuario'])
            ->orderBy('fecha', 'desc')
            ->get();
        Log::info('Citas encontradas:', ['cliente_id' => $cliente->id, 'citas' => $citas]);

        return response()->json($citas);
    }
}
