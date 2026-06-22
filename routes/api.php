<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;

// Rutas Públicas de la API
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas de la API
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Perfil
    Route::get('/user', function (Request $request) {
        // Cargar los roles asociados para que la App sepa qué interfaz mostrar
        return response()->json([
            'user' => $request->user(),
            'role' => $request->user()->roles->first()->name ?? 'usuario'
        ]);
    });

    // Módulo de Tickets
    Route::get('/tickets/opciones', [TicketController::class, 'getFormOptions']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);

    // Módulo de Base de Conocimientos (Solo Técnicos/Gestores)
    Route::get('/conocimiento', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'index']);

    // Módulo Gestor
    Route::get('/gestor/opciones-asignacion', [\App\Http\Controllers\Api\GestorController::class, 'getOpcionesAsignacion']);
    Route::post('/gestor/tickets/{id}/asignar', [\App\Http\Controllers\Api\GestorController::class, 'asignarTicket']);

    // Módulo Técnico
    Route::post('/tecnico/tickets/{id}/comentar', [\App\Http\Controllers\Api\TecnicoController::class, 'comentar']);
    Route::post('/tecnico/tickets/{id}/resolver', [\App\Http\Controllers\Api\TecnicoController::class, 'resolverTicket']);
});
