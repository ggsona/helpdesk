<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\NivelJerarquico;
use App\Models\UnidadAdministrativa;
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
        $permVerConfig         = Permission::updateOrCreate(['name' => 'ver-configuraciones']);
        $permGestionarUsuarios = Permission::updateOrCreate(['name' => 'gestionar-usuarios']);

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
        // 2.1 Niveles Jerárquicos (Amplio catálogo)
        $nomenclaturas = [
            'Sede', 'Complejo', 'División', 'Área', 'Departamento',
            'Oficina', 'Sección', 'Grupo', 'Equipo', 'Unidad'
        ];
        
        $nivelesList = [];
        foreach ($nomenclaturas as $index => $nombre) {
            $nivelesList[$nombre] = NivelJerarquico::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'nivel' => $index + 1,
                    // Activar solo los 3 primeros por defecto para no saturar al usuario
                    'is_active' => $index < 3 
                ]
            );
        }

        // 2.2 Unidades Administrativas (Organigrama)
        $sedeCentral = UnidadAdministrativa::firstOrCreate(['nombre' => 'Sede Central', 'id_nivel' => $nivelesList['Sede']->id]);
        $sucursalNorte = UnidadAdministrativa::firstOrCreate(['nombre' => 'Sucursal Norte', 'id_nivel' => $nivelesList['Sede']->id]);
        
        $deptTech = UnidadAdministrativa::firstOrCreate(['nombre' => 'Departamento de Tecnología', 'id_nivel' => $nivelesList['División']->id, 'parent_id' => $sedeCentral->id]);
        UnidadAdministrativa::firstOrCreate(['nombre' => 'Soporte Nivel 1', 'id_nivel' => $nivelesList['Área']->id, 'parent_id' => $deptTech->id]);

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
            ['telefono' => '00000000', 'id_unidad_administrativa' => $sedeCentral->id]
        );

        $personaGestor = Persona::firstOrCreate(
            ['nombre' => 'Gestor', 'apellido' => 'De Soporte'],
            ['telefono' => '12345678', 'id_unidad_administrativa' => $sedeCentral->id]
        );

        $personaTecnico = Persona::firstOrCreate(
            ['nombre' => 'Tecnico', 'apellido' => 'General'],
            ['telefono' => '00000000', 'id_unidad_administrativa' => $deptTech->id]
        );

        $personaUsuario = Persona::firstOrCreate(
            ['nombre' => 'usuario', 'apellido' => 'Final'],
            ['telefono' => '00000000', 'id_unidad_administrativa' => $sucursalNorte->id]
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
                'is_approved' => true,
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
                'is_approved' => true,
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
                'is_approved' => true,
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
                'is_approved' => true,
            ]
        );
        $usuarioFinal->assignRole($roleUsuario);
    }
}