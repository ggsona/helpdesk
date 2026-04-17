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

Route::middleware(['auth', 'role:cliente'])->group(function () {
    // El Home del cliente
    Route::get('/dashboard', [TicketClienteController::class, 'home'])->name('dashboard');
    
    // Lista de tickets
    Route::get('/mis-tickets', [TicketClienteController::class, 'index'])->name('cliente.tickets.index');
    
    // Formulario de creación
    Route::get('/mis-tickets/nuevo', [TicketClienteController::class, 'create'])->name('cliente.tickets.create');
    
    // Guardar ticket
    Route::post('/mis-tickets', [TicketClienteController::class, 'store'])->name('cliente.tickets.store');
});

require __DIR__.'/auth.php';
