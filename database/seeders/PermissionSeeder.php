<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia la caché de permisos de Spatie antes de crear/actualizar
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -----------------------------------------------------------------------
        // 1. DEFINICIÓN DE TODOS LOS PERMISOS DEL SISTEMA
        // -----------------------------------------------------------------------
        $permisos = [

            // --- Tickets ---
            'crear-tickets'           => 'Crear nuevos tickets de soporte',
            'ver-panel-operativo'     => 'Ver el panel operativo de tickets (mesa de despacho)',
            'asignar-tickets'         => 'Asignar tickets a técnicos',
            'resolver-tickets'        => 'Marcar tickets como resueltos',
            'comentar-interno'        => 'Escribir comentarios internos en tickets',
            'cerrar-tickets'          => 'Cerrar tickets finalizados',
            'reabrir-tickets'         => 'Reabrir tickets cerrados',

            // --- Usuarios y Roles ---
            'gestionar-usuarios'      => 'Crear, editar y eliminar usuarios del sistema',
            'gestionar-roles'         => 'Gestionar roles y permisos del sistema',
            'aprobar-usuarios'        => 'Aprobar o rechazar solicitudes de registro',

            // --- Configuración y Catálogos ---
            'ver-configuraciones'     => 'Ver el módulo de configuraciones generales',
            'gestionar-categorias'    => 'Crear, editar y eliminar categorías de tickets',
            'gestionar-catalogos'     => 'Gestionar catálogos (marcas, modelos, etc.)',

            // --- Equipos / Inventario ---
            'gestionar-equipos'       => 'Gestionar equipos del inventario (CRUD)',
            'ver-equipos'             => 'Ver el inventario de equipos',

            // --- Reportes y Auditoría ---
            'ver-rendimiento-tecnico' => 'Ver estadísticas de rendimiento por técnico',
            'ver-auditorias'          => 'Ver la bitácora completa de auditorías',
            'exportar-reportes'       => 'Exportar reportes a PDF u otros formatos',

            // --- Base de Conocimiento (Acceso) ---
            'ver-conocimiento'        => 'Acceder y buscar artículos en la Base de Conocimiento',

            // --- Base de Conocimiento (Acciones Granulares) ---
            // Permiso individual → permite combinar accesos exactos por rol.
            // Ej: un técnico puede crear y editar pero no archivar ni eliminar.
            'crear-articulo'          => 'Crear nuevos artículos en la Base de Conocimiento',
            'editar-articulo'         => 'Editar artículos existentes en la Base de Conocimiento',
            'archivar-articulo'       => 'Archivar artículos (los retira de la vista pública, no los borra)',
            'gestionar-tags'          => 'Crear, editar y eliminar etiquetas (tags) de artículos',
            'imprimir-articulo'       => 'Generar e imprimir artículos en formato PDF',

            // --- Base de Conocimiento (Solo Admin) ---
            // Elimina de forma permanente (soft delete). No asignar a roles operativos.
            'eliminar-articulo'       => 'Eliminar permanentemente artículos de la Base de Conocimiento (solo Admin)',
        ];

        // Crear o actualizar cada permiso
        foreach ($permisos as $nombre => $descripcion) {
            Permission::updateOrCreate(
                ['name' => $nombre],
                ['name' => $nombre, 'guard_name' => 'web']
            );
        }

        // -----------------------------------------------------------------------
        // 2. ASIGNACIÓN DE PERMISOS A ROLES
        // -----------------------------------------------------------------------

        $roleAdmin   = Role::firstOrCreate(['name' => 'admin']);
        $roleGestor  = Role::firstOrCreate(['name' => 'gestor']);
        $roleTecnico = Role::firstOrCreate(['name' => 'tecnico']);
        $roleUsuario = Role::firstOrCreate(['name' => 'usuario']);

        // --- Rol USUARIO: crear tickets + ver y imprimir KB ---
        $roleUsuario->syncPermissions([
            'crear-tickets',
            'ver-conocimiento',
            'imprimir-articulo',
        ]);

        // --- Rol TÉCNICO: operación de tickets + gestión parcial de KB ---
        $roleTecnico->syncPermissions([
            'ver-panel-operativo',
            'resolver-tickets',
            'comentar-interno',
            'cerrar-tickets',
            'ver-equipos',
            // Base de Conocimiento: puede ver, crear y editar, pero no archivar
            'ver-conocimiento',
            'crear-articulo',
            'editar-articulo',
            'imprimir-articulo',
        ]);

        // --- Rol GESTOR: gestión operativa completa + KB completa (sin eliminación) ---
        $roleGestor->syncPermissions([
            'ver-panel-operativo',
            'asignar-tickets',
            'comentar-interno',
            'cerrar-tickets',
            'reabrir-tickets',
            'aprobar-usuarios',
            'gestionar-equipos',
            'ver-equipos',
            'gestionar-catalogos',
            'ver-rendimiento-tecnico',
            'ver-auditorias',
            'exportar-reportes',
            // Base de Conocimiento: acceso completo excepto eliminación permanente
            'ver-conocimiento',
            'crear-articulo',
            'editar-articulo',
            'archivar-articulo',
            'gestionar-tags',
            'imprimir-articulo',
        ]);

        // --- Rol ADMIN: TODOS los permisos del sistema (incluye eliminar-articulo) ---
        $roleAdmin->syncPermissions(Permission::all());

        $this->command->info('✅ Permisos creados y asignados correctamente.');
        $this->command->table(
            ['Rol', 'Nº Permisos'],
            [
                ['Admin',   $roleAdmin->permissions()->count()],
                ['Gestor',  $roleGestor->permissions()->count()],
                ['Técnico', $roleTecnico->permissions()->count()],
                ['Usuario', $roleUsuario->permissions()->count()],
            ]
        );
    }
}

