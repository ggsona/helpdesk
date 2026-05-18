<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UsuarioAprobacionController extends Controller
{
    /**
     * Listar los usuarios pendientes de aprobación.
     */
    public function index()
    {
        // Traemos todos los usuarios con is_approved = false y cargamos sus relaciones de Persona y Unidad
        $usuariosPendientes = User::where('is_approved', false)
            ->with(['persona.unidadAdministrativa.nivel'])
            ->get();

        // Obtenemos los roles disponibles en el sistema (ej: usuario, técnico, gestor, admin)
        // para dar la opción de cambiar el rol al momento de aprobar.
        $roles = Role::all();

        return view('admin.usuarios.pendientes', compact('usuariosPendientes', 'roles'));
    }

    /**
     * Aprobar la entrada de un usuario y opcionalmente cambiar su rol.
     */
    public function aprobar(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_name' => 'required|exists:roles,name'
        ]);

        // 1. Activar al usuario
        $user->is_approved = true;

        // 2. Si se seleccionó un rol diferente, reasignarlo
        // Removemos los roles anteriores (por si acaso) y asignamos el nuevo
        $user->syncRoles([$request->role_name]);

        // Ajustamos la columna "role" de apoyo que tienes en tu base de datos (según tu DatabaseSeeder)
        // 1: Admin, 2: Gestor, 3: Usuario, 4: Técnico (o el valor que corresponda en tu lógica)
        switch ($request->role_name) {
            case 'admin':
                $user->role = '1';
                break;
            case 'gestor':
                $user->role = '2';
                break;
            case 'tecnico':
                $user->role = '4';
                break;
            default:
                $user->role = '3'; // usuario normal
                break;
        }

        $user->save();

        return redirect()->back()->with('success', 'El usuario ' . $user->name . ' ha sido aprobado y activado con el rol ' . strtoupper($request->role_name) . '.');
    }

    /**
     * Rechazar e inactivar/eliminar la solicitud de un usuario.
     */
    public function rechazar($id)
    {
        $user = User::findOrFail($id);
        $persona = $user->persona;

        // Para resguardar la seguridad y limpieza de la base de datos,
        // eliminamos al usuario y a su persona asociada si se rechaza su solicitud.
        $nombre = $user->name;
        
        $user->delete();
        if ($persona) {
            $persona->delete();
        }

        return redirect()->back()->with('success', 'La solicitud de ' . $nombre . ' ha sido rechazada y eliminada del sistema.');
    }
}
