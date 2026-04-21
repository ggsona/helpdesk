<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// Controlador actualizado
use App\Http\Controllers\Usuario\TicketUsuarioController; 
use App\Http\Controllers\Gestor\TicketGestorController;

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
Route::middleware(['auth', 'role:cliente'])->group(function () {
    
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

// --- RUTAS DE GESTOR ---
Route::middleware(['auth', 'role:gestor'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// --- RUTAS DE ADMIN ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// --- RUTAS DE TECNICO ---
Route::middleware(['auth', 'role:tecnico'])->prefix('tecnico')->name('tecnico.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// --- GESTIÓN PARA GESTOR/ADMIN ---
Route::middleware(['auth', 'role:gestor|admin'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('/tickets', [TicketGestorController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{id}/asignar', [TicketGestorController::class, 'asignar'])->name('tickets.asignar');
});

require __DIR__.'/auth.php';