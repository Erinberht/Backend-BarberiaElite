<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\EmpleadoController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::apiResource('servicios', ServicioController::class);
Route::post('/servicios/{id}/especialidades', [ServicioController::class, 'asignarEspecialidades']);

// Public empleados route for clients to book appointments
Route::get('/empleados', [EmpleadoController::class, 'index']);

Route::middleware('auth:sanctum')->post('/citas', [CitaController::class, 'store']);
Route::middleware('auth:sanctum')->get('/citas', [CitaController::class, 'index']);
Route::middleware('auth:sanctum')->put('/citas/{id}', [CitaController::class, 'update']);

Route::apiResource('clientes', ClienteController::class);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/empleados', [AdminController::class, 'listarEmpleados']);
    Route::post('/empleados', [AdminController::class, 'registrarEmpleado']);
    Route::put('/empleados/{id}', [AdminController::class, 'actualizarEmpleado']);
    Route::delete('/empleados/{id}', [AdminController::class, 'eliminarEmpleado']);
    Route::post('/empleados/{id}/especialidades', [AdminController::class, 'asignarEspecialidades']);
});

Route::middleware(['auth:sanctum', 'rol:cliente'])->group(function () {
    Route::get('/cliente/citas', [ClienteController::class, 'misCitas']);
    Route::put('/citas/{id}/cancelar', [CitaController::class, 'cancelar']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/empleados/{id}/horarios', [EmpleadoController::class, 'getHorariosDisponibles']);
    Route::get('/empleados/{id}/fechas-disponibles', [EmpleadoController::class, 'getFechasDisponibles']);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Sesión cerrada']);
});

Route::middleware('auth:sanctum')->get('/tokens', function (Request $request) {
    return $request->user()->tokens;
});

Route::middleware(['auth:sanctum', 'rol:empleado'])->group(function () {
    Route::get('/empleado/mis-citas', [EmpleadoController::class, 'misCitas']);
});

Route::apiResource('especialidades', App\Http\Controllers\Api\EspecialidadController::class);
