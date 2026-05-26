<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Proteger el controlador para que solo administradores puedan ingresar.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Listar todos los roles y sus permisos.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Formulario para crear un nuevo rol.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Guardar el nuevo rol y sincronizar sus permisos seleccionados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Crear rol (Spatie por defecto requiere el nombre en minúsculas)
        $roleName = strtolower(trim($request->name));
        $role = Role::create(['name' => $roleName]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => get_class($role),
                'auditable_id' => $role->id,
                'action' => 'sync_permissions',
                'old_values' => null,
                'new_values' => ['permissions' => $permissions->pluck('name')->toArray()],
                'ip_address' => request() ? request()->ip() : null,
                'user_agent' => request() ? request()->userAgent() : null,
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "El rol '{$role->name}' se ha creado y configurado con éxito.");
    }

    /**
     * Formulario para editar un rol y ajustar sus permisos.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Actualizar el rol y sincronizar sus permisos.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Evitar cambiar el nombre de los roles del sistema base para evitar romper la lógica
        $rolesProtegidos = ['admin', 'gestor', 'tecnico', 'usuario'];
        $esProtegido = in_array($role->name, $rolesProtegidos);

        $request->validate([
            'name' => $esProtegido ? 'required|string|in:' . $role->name : 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (!$esProtegido) {
            $role->name = strtolower(trim($request->name));
            $role->save();
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();

        // Sincronizar permisos (los no seleccionados se quitan automáticamente)
        $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();
        $role->syncPermissions($permissions);

        $newPermissions = $permissions->pluck('name')->toArray();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($role),
            'auditable_id' => $role->id,
            'action' => 'sync_permissions',
            'old_values' => ['permissions' => $oldPermissions],
            'new_values' => ['permissions' => $newPermissions],
            'ip_address' => request() ? request()->ip() : null,
            'user_agent' => request() ? request()->userAgent() : null,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', "El rol '{$role->name}' y sus permisos se han actualizado correctamente.");
    }

    /**
     * Eliminar un rol de forma segura.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Proteger los roles vitales del sistema
        $rolesProtegidos = ['admin', 'gestor', 'tecnico', 'usuario'];
        if (in_array($role->name, $rolesProtegidos)) {
            return redirect()->route('admin.roles.index')
                ->with('error', "No es posible eliminar el rol básico del sistema '{$role->name}' por motivos de seguridad.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "El rol se ha eliminado correctamente del sistema.");
    }
}
