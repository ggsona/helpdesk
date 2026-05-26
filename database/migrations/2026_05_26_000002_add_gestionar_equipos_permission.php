<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar la caché de Spatie para evitar inconsistencias
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear el permiso
        $permGestionarEquipos = Permission::updateOrCreate(['name' => 'gestionar-equipos']);

        // Asignar al rol Admin (admin tiene syncPermissions(Permission::all()) en el seeder,
        // pero vamos a asignarlo explícitamente para asegurar que se agregue ahora)
        $roleAdmin = Role::findByName('admin');
        if ($roleAdmin) {
            $roleAdmin->givePermissionTo($permGestionarEquipos);
        }

        // Asignar al rol Gestor
        $roleGestor = Role::findByName('gestor');
        if ($roleGestor) {
            $roleGestor->givePermissionTo($permGestionarEquipos);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpiar caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Remover el permiso
        $permission = Permission::findByName('gestionar-equipos');
        if ($permission) {
            $permission->delete();
        }
    }
};
