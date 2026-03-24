<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\DiarioController;
use App\Http\Controllers\CriseController;
use App\Http\Controllers\ResumoController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\ExameController;
use App\Http\Controllers\RegistroUsoController;
use App\Http\Controllers\PerfilCrohnController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('me', [AuthController::class, 'updateProfile']);

    Route::apiResource('medicamentos', MedicamentoController::class);
    Route::put('medicamentos/{medicamento}/estoque', [EstoqueController::class, 'update']);
    Route::post('medicamentos/{medicamento}/reabastecer', [EstoqueController::class, 'reabastecer']);

    Route::get('diarios/exportar', [DiarioController::class, 'exportar']);
    Route::apiResource('diarios', DiarioController::class)->except(['show']);

    Route::apiResource('crises', CriseController::class)->except(['show'])->parameters(['crises' => 'crise']);

    Route::get('tags', [TagController::class, 'index']);

    Route::get('resumo', [ResumoController::class, 'index']);

    Route::post('device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);

    Route::get('avisos', [AvisoController::class, 'index']);
    Route::get('avisos/nao-lidos', [AvisoController::class, 'naoLidos']);
    Route::put('avisos/{id}/lido', [AvisoController::class, 'marcarLido']);
    Route::put('avisos/marcar-todos', [AvisoController::class, 'marcarTodosLidos']);

    Route::post('perfil-crohn', [PerfilCrohnController::class, 'store']);
    Route::get('perfil-crohn', [PerfilCrohnController::class, 'show']);

    Route::get('perfil/stats', [PerfilController::class, 'stats']);

    Route::apiResource('consultas', ConsultaController::class)->except(['show']);

    Route::apiResource('exames', ExameController::class)->except(['show', 'destroy']);

    Route::post('registros-uso', [RegistroUsoController::class, 'store']);
    Route::get('medicamentos/{medicamento}/registros-uso', [RegistroUsoController::class, 'index']);
});
