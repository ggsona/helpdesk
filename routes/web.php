<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Cliente\TicketClienteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
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

// --- RUTAS DE CLIENTE ---
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/dashboard', [TicketClienteController::class, 'home'])->name('dashboard');
    Route::get('/mis-tickets', [TicketClienteController::class, 'index'])->name('cliente.tickets.index');
    Route::get('/mis-tickets/nuevo', [TicketClienteController::class, 'create'])->name('cliente.tickets.create');
    Route::post('/mis-tickets', [TicketClienteController::class, 'store'])->name('cliente.tickets.store');
});

// --- RUTAS DE ADMIN ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    // Agrega aquí más rutas de admin
});

// --- RUTAS DE TECNICO ---
Route::middleware(['auth', 'role:tecnico'])->group(function () {
    Route::get('/tecnico/dashboard', [DashboardController::class, 'index'])->name('tecnico.dashboard');
    // Agrega aquí más rutas de técnico
});

require __DIR__.'/auth.php';
