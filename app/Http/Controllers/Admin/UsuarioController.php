<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios activos y registrados del sistema.
     */
    public function index()
    {
        // Traemos todos los usuarios registrados con sus relaciones y roles
        $usuarios = User::with(['persona.unidadAdministrativa.nivel', 'roles'])
            ->orderBy('name', 'asc')
            ->get();

        // Obtenemos todos los roles de Spatie para poder asignárselos en la edición
        $roles = Role::all();

        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Actualizar el rol de un usuario existente.
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Evitar que el administrador se cambie el rol a sí mismo
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Seguridad: No puedes cambiar tu propio rol.');
        }

        $request->validate([
            'role_name' => 'required|exists:roles,name'
        ]);

        // Sincronizar rol de Spatie
        $user->syncRoles([$request->role_name]);

        // Ajustamos la columna "role" de apoyo que tienes en tu base de datos (según tu DatabaseSeeder)
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

        return redirect()->back()->with('success', 'El rol de ' . $user->name . ' ha sido actualizado a ' . strtoupper($request->role_name) . ' con éxito.');
    }

    /**
     * Alternar el estado de aprobación (Activo/Inactivo) de un usuario.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Evitar que el administrador se desactive a sí mismo
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Seguridad: No puedes desactivar tu propia cuenta.'
            ], 403);
        }

        // Alternar el estado is_approved
        $user->is_approved = !$user->is_approved;
        $user->save();

        return response()->json([
            'success' => true,
            'is_approved' => $user->is_approved,
            'message' => $user->is_approved ? 'Usuario activado exitosamente.' : 'Usuario desactivado exitosamente.'
        ]);
    }
}
