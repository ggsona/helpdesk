<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Oficina;
use App\Models\Prioridad;
use App\Models\Categoria;
use App\Models\TipoEquipo;
use App\Models\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
// Importamos los modelos de roles y permisos de Spatie
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. CONFIGURACIÓN DE ROLES (Spatie) ---
        // Los creamos primero para poder asignarlos después
        $roleAdmin   = Role::updateOrCreate(['id' => 1], ['name' => 'admin']);
        $roleGestor  = Role::updateOrCreate(['id' => 2], ['name' => 'gestor']);
        $roleTecnico = Role::updateOrCreate(['id' => 3], ['name' => 'tecnico']);
        $roleUsuario = Role::updateOrCreate(['id' => 4], ['name' => 'usuario']);

        // --- 1.2 CONFIGURACIÓN DE PERMISOS (Spatie) ---
        $permCrearTickets      = Permission::updateOrCreate(['name' => 'crear-tickets']);
        $permVerPanelOperativo = Permission::updateOrCreate(['name' => 'ver-panel-operativo']);
        $permAsignarTickets    = Permission::updateOrCreate(['name' => 'asignar-tickets']);
        $permResolverTickets   = Permission::updateOrCreate(['name' => 'resolver-tickets']);
        $permComentarInterno   = Permission::updateOrCreate(['name' => 'comentar-interno']);
        $permGestionarRoles    = Permission::updateOrCreate(['name' => 'gestionar-roles']);

        // --- 1.3 ASIGNACIÓN DE PERMISOS A ROLES ---
        // Rol Usuario
        $roleUsuario->syncPermissions([$permCrearTickets]);

        // Rol Técnico
        $roleTecnico->syncPermissions([
            $permVerPanelOperativo,
            $permResolverTickets,
            $permComentarInterno
        ]);

        // Rol Gestor
        $roleGestor->syncPermissions([
            $permVerPanelOperativo,
            $permAsignarTickets,
            $permComentarInterno
        ]);

        // Rol Admin (Tiene todos los permisos)
        $roleAdmin->syncPermissions(Permission::all());

        // --- 2. CONFIGURACIÓN DE TABLAS MAESTRAS ---
        Oficina::firstOrCreate(['nombre_oficina' => 'Sede Central']);
        Oficina::firstOrCreate(['nombre_oficina' => 'Sucursal Norte']);

        Prioridad::firstOrCreate(['nombre_prioridad' => 'Baja']);
        Prioridad::firstOrCreate(['nombre_prioridad' => 'Media']);
        Prioridad::firstOrCreate(['nombre_prioridad' => 'Alta']);
        Prioridad::firstOrCreate(['nombre_prioridad' => 'Crítica']);

        Categoria::firstOrCreate(['nombre_categoria' => 'Hardware']);
        Categoria::firstOrCreate(['nombre_categoria' => 'Software']);
        Categoria::firstOrCreate(['nombre_categoria' => 'Redes']);

        TipoEquipo::firstOrCreate(['nombre_tipo_equipo' => 'Laptop']);
        TipoEquipo::firstOrCreate(['nombre_tipo_equipo' => 'Desktop']);
        TipoEquipo::firstOrCreate(['nombre_tipo_equipo' => 'Impresora']);

        // --- 3. CREACIÓN DE PERSONAS ---
        $personaAdmin = Persona::firstOrCreate(
            ['nombre' => 'Admin', 'apellido' => 'General'],
            ['telefono' => '00000000', 'id_oficina' => 1]
        );

        $personaGestor = Persona::firstOrCreate(
            ['nombre' => 'Gestor', 'apellido' => 'De Soporte'],
            ['telefono' => '12345678', 'id_oficina' => 1]
        );

        $personaTecnico = Persona::firstOrCreate(
            ['nombre' => 'Tecnico', 'apellido' => 'General'],
            ['telefono' => '00000000', 'id_oficina' => 1]
        );

        $personaUsuario = Persona::firstOrCreate(
            ['nombre' => 'usuario', 'apellido' => 'Final'],
            ['telefono' => '00000000', 'id_oficina' => 2]
        );

        // --- 4. CREACIÓN DE USUARIOS Y ASIGNACIÓN DE ROLES ---
        
        // Crear Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@helpdesk.com'],
            [
                'name' => 'Administrador',
                'role' => '1',
                'password' => Hash::make('admin123'),
                'id_persona' => $personaAdmin->id_persona,
            ]
        );
        $admin->assignRole($roleAdmin);

        // Crear Gestor
        $gestor = User::updateOrCreate(
            ['email' => 'gestor@helpdesk.com'],
            [
                'name' => 'Gestor de Soporte',
                'role' => '2',
                'password' => Hash::make('gestor123'),
                'id_persona' => $personaGestor->id_persona,
            ]
        );
        $gestor->assignRole($roleGestor);

        // Crear Técnico
        $tecnico = User::updateOrCreate(
            ['email' => 'tecnico@helpdesk.com'],
            [
                'name' => 'Tecnico',
                'role' => '3',
                'password' => Hash::make('tecnico123'),
                'id_persona' => $personaTecnico->id_persona,
            ]
        );
        $tecnico->assignRole($roleTecnico);

        // Crear Usuario
        $usuarioFinal = User::updateOrCreate(
            ['email' => 'usuario@helpdesk.com'],
            [
                'name' => 'Usuario GDC',
                'role' => '4',
                'password' => Hash::make('usuario123'),
                'id_persona' => $personaUsuario->id_persona,
            ]
        );
        $usuarioFinal->assignRole($roleUsuario);
    }
}