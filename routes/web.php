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

// Endpoint público para cascada AJAX del registro
Route::get('/unidades-hijas/{parentId}', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'getChildrenUnidades'])->name('unidades.hijas');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

// Sala de Espera (Aprobación Pendiente)
Route::get('/espera', function () {
    return view('auth.awaiting-approval');
})->middleware('auth')->name('awaiting-approval');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- RUTAS DE USUARIO ---
Route::middleware(['auth', 'approved', 'role:usuario'])->group(function () {
    
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
Route::middleware(['auth', 'approved'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:ver-panel-operativo');
    
    // --- Control de Roles y Permisos ---
    Route::middleware('can:gestionar-roles')->group(function () {
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    });
    
    // --- Gestión General de Usuarios y Aprobaciones ---
    Route::middleware('can:gestionar-usuarios')->group(function () {
        // Aprobaciones
        Route::get('/usuarios/pendientes', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'index'])->name('usuarios.pendientes');
        Route::post('/usuarios/pendientes/{id}/aprobar', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'aprobar'])->name('usuarios.aprobar');
        Route::delete('/usuarios/pendientes/{id}/rechazar', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'rechazar'])->name('usuarios.rechazar');
        
        // Directorio de Usuarios
        Route::get('/usuarios', [\App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/{id}/update-role', [\App\Http\Controllers\Admin\UsuarioController::class, 'updateRole'])->name('usuarios.update-role');
        Route::post('/usuarios/{id}/toggle', [\App\Http\Controllers\Admin\UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle');
    });
    
    // --- Estructura Organizacional y Configuraciones ---
    Route::middleware('can:ver-configuraciones')->group(function () {
        Route::get('/estructura', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'index'])->name('estructura.index');
        Route::post('/estructura/unidades', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'storeUnidad'])->name('estructura.unidades.store');
        Route::put('/estructura/unidades/{id}', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'updateUnidad'])->name('estructura.unidades.update');
        Route::delete('/estructura/unidades/{id}', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'destroyUnidad'])->name('estructura.unidades.destroy');

        Route::get('/configuraciones', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'index'])->name('configuraciones.index');
        Route::post('/configuraciones/niveles/reorder', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'reorderNiveles'])->name('configuraciones.niveles.reorder');
        Route::post('/configuraciones/niveles/{id}/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleNivel'])->name('configuraciones.niveles.toggle');
        Route::post('/configuraciones/ad', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'updateAd'])->name('configuraciones.ad.update');
        Route::post('/configuraciones/ad/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleAd'])->name('configuraciones.ad.toggle');
        Route::post('/configuraciones/ad/test', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'testAd'])->name('configuraciones.ad.test');
        Route::post('/configuraciones/niveles', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'storeNivel'])->name('configuraciones.niveles.store');
    });
});

// --- RUTAS UNIFICADAS DE SOPORTE (COORDINADORES Y TÉCNICOS) ---
Route::middleware(['auth', 'approved', 'can:ver-panel-operativo'])->prefix('soporte')->name('soporte.')->group(function () {
    
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