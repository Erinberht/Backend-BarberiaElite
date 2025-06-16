<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Registro
    public function register(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|unique:users',
            'password' => 'required|min:6',
            'rol' => 'required|in:cliente,empleado,admin',
            'nombre' => 'required',
            'correo' => 'required|email|unique:users,correo',
            'telefono' => 'nullable|string',
        ]);

        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario,
            'password' => bcrypt($request->password),
            'rol' => $request->rol,
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
        ]);

        // Si el rol es cliente, crear también el registro en la tabla clientes
        if ($request->rol === 'cliente') {
            \App\Models\Cliente::create([
                'usuario_id' => $user->id
            ]);
        }

        return response()->json(['message' => 'Usuario registrado con éxito'], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('nombre_usuario', $request->nombre_usuario)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'nombre_usuario' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    // Obtener usuario autenticado
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function tokensActivos(Request $request)
    {
        $user = auth()->user();
        return $user->tokens; // Devuelve una colección de tokens activos

    }
}
