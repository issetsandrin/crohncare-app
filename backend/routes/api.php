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
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

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
});
