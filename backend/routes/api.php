<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\OrdenTrabajoController;
use App\Http\Controllers\Api\EstadoOrdenController;
use App\Http\Controllers\Api\TipoServicioController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Api\ServicioAdicionalController;
use App\Http\Controllers\Api\DetalleServicioOrdenController;


// AUTENTICACIÓN
Route::post('/login', [AuthController::class, 'login']);

// ROLES
Route::prefix('roles')->group(function () {
    Route::get('/', [RolController::class, 'index']);
});

// USUARIOS
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::put('/{id}', [UsuarioController::class, 'update']);
    Route::delete('/{id}', [UsuarioController::class, 'destroy']);
});

//CLIENTES
Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/', [ClienteController::class, 'store']);
    Route::get('/{id}', [ClienteController::class, 'show']); // opcional pero recomendado
    Route::put('/{id}', [ClienteController::class, 'update']);
    Route::delete('/{id}', [ClienteController::class, 'destroy']); // opcional
});

//  ÓRDENES DE TRABAJO
Route::prefix('ordenes-trabajo')->group(function () {
    Route::get('/', [OrdenTrabajoController::class, 'index']);
    Route::post('/', [OrdenTrabajoController::class, 'store']);
    Route::get('/{id}', [OrdenTrabajoController::class, 'show']);
    Route::put('/{id}', [OrdenTrabajoController::class, 'update']);
});

// ESTADOS DE ORDEN
Route::prefix('estados-orden')->group(function () {
    Route::get('/', [EstadoOrdenController::class, 'index']);
    Route::post('/', [EstadoOrdenController::class, 'store']);
    Route::get('/{id}', [EstadoOrdenController::class, 'show']);
    Route::put('/{id}', [EstadoOrdenController::class, 'update']);
    Route::delete('/{id}', [EstadoOrdenController::class, 'destroy']);
});

// TIPOS DE SERVICIO
Route::prefix('tipos-servicio')->group(function () {
    Route::get('/', [TipoServicioController::class, 'index']);
    Route::post('/', [TipoServicioController::class, 'store']);
    Route::get('/{id}', [TipoServicioController::class, 'show']);
    Route::put('/{id}', [TipoServicioController::class, 'update']);
    Route::delete('/{id}', [TipoServicioController::class, 'destroy']);
});

// VEHÍCULOS (solo listado)
Route::prefix('vehiculos')->group(function () {
    Route::get('/', [VehiculoController::class, 'index']);
    Route::get('/{id}', [VehiculoController::class, 'show']);
});

// SERVICIOS ADICIONALES
Route::prefix('servicios-adicionales')->group(function () {
    Route::get('/', [ServicioAdicionalController::class, 'index']);
    Route::post('/', [ServicioAdicionalController::class, 'store']);
    Route::get('/{id}', [ServicioAdicionalController::class, 'show']);
    Route::put('/{id}', [ServicioAdicionalController::class, 'update']);
    Route::delete('/{id}', [ServicioAdicionalController::class, 'destroy']);
});

// DETALLES DE SERVICIO POR ORDEN
Route::prefix('detalle-servicio-orden')->group(function () {
    Route::get('/', [DetalleServicioOrdenController::class, 'index']);
    Route::post('/', [DetalleServicioOrdenController::class, 'store']);
    Route::get('/{id}', [DetalleServicioOrdenController::class, 'show']);
    Route::put('/{id}', [DetalleServicioOrdenController::class, 'update']);
    Route::delete('/{id}', [DetalleServicioOrdenController::class, 'destroy']);
});
