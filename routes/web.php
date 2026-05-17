<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// Controlador actualizado
use App\Http\Controllers\Usuario\TicketUsuarioController; 
use App\Http\Controllers\Gestor\TicketGestorController;
use App\Http\Controllers\Tecnico\TicketTecnicoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- RUTAS DE USUARIO ---
Route::middleware(['auth', 'role:usuario'])->group(function () {
    
    // Redirección interna para el usuario
    Route::get('/usuario/dashboard', [TicketUsuarioController::class, 'home'])->name('usuario.home');
    
    // Gestión de Tickets del Usuario usando TicketUsuarioController
    Route::get('/mis-tickets', [TicketUsuarioController::class, 'index'])->name('usuario.tickets.index');
    Route::get('/mis-tickets/nuevo', [TicketUsuarioController::class, 'create'])->name('usuario.tickets.create');
    Route::post('/mis-tickets', [TicketUsuarioController::class, 'store'])->name('usuario.tickets.store');
    Route::get('/mis-tickets/{ticket}', [TicketUsuarioController::class, 'show'])->name('usuario.tickets.show');
    Route::get('/mis-tickets/{ticket}/editar', [TicketUsuarioController::class, 'edit'])->name('usuario.tickets.edit');
    
    // Acciones específicas
    Route::post('/mis-tickets/{ticket}/enviar', [TicketUsuarioController::class, 'enviar'])->name('usuario.tickets.enviar');
    Route::put('/tickets/{id}', [TicketUsuarioController::class, 'update'])->name('usuario.tickets.update');
    Route::delete('/tickets/{id}', [TicketUsuarioController::class, 'destroy'])->name('usuario.tickets.destroy');
    Route::post('/tickets/{id}/comentar', [TicketUsuarioController::class, 'storeComentario'])->name('usuario.tickets.comentar');
});

// --- RUTAS DE ADMIN (ADMINISTRACIÓN GLOBAL DEL SISTEMA) ---
Route::middleware(['auth', 'can:gestionar-roles'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
});

// --- RUTAS UNIFICADAS DE SOPORTE (COORDINADORES Y TÉCNICOS) ---
Route::middleware(['auth', 'can:ver-panel-operativo'])->prefix('soporte')->name('soporte.')->group(function () {
    
    // Vista de Dashboard de Soporte
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Subgrupo para Coordinación (Gestores / Administradores con permiso 'asignar-tickets')
    Route::middleware('can:asignar-tickets')->group(function () {
        Route::get('/tickets', [TicketGestorController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/{id}/asignar', [TicketGestorController::class, 'asignar'])->name('tickets.asignar');
        Route::get('/tickets/{id}', [TicketGestorController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/comentar', [TicketGestorController::class, 'comentar'])->name('tickets.comentar');
    });

    // Subgrupo para Especialistas Técnicos (con permiso 'resolver-tickets')
    Route::middleware('can:resolver-tickets')->prefix('tecnico')->name('tickets.tecnico.')->group(function () {
        Route::get('/tickets', [TicketTecnicoController::class, 'index'])->name('index');
        Route::get('/tickets/{id}', [TicketTecnicoController::class, 'show'])->name('show');
        Route::post('/tickets/{id}/comentar', [TicketTecnicoController::class, 'comentar'])->name('comentar');
        
        // Acciones de resolución
        Route::get('/tickets/{id}/resolver', [TicketTecnicoController::class, 'crearSolucion'])->name('resolver');
        Route::post('/tickets/{id}/guardar-solucion', [TicketTecnicoController::class, 'guardarSolucion'])->name('guardar-solucion');
        Route::get('/tickets/{id}/editar-solucion', [TicketTecnicoController::class, 'editarSolucion'])->name('editar-solucion');
        Route::put('/tickets/{id}/actualizar-solucion', [TicketTecnicoController::class, 'actualizarSolucion'])->name('actualizar-solucion');
    });
});

require __DIR__.'/auth.php';