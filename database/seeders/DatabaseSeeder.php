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
// Importamos el modelo de roles de Spatie
use Spatie\Permission\Models\Role;

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

        // --- 2. CONFIGURACIÓN DE TABLAS MAESTRAS ---
        Oficina::create(['nombre_oficina' => 'Sede Central']);
        Oficina::create(['nombre_oficina' => 'Sucursal Norte']);

        Prioridad::create(['nombre_prioridad' => 'Baja']);
        Prioridad::create(['nombre_prioridad' => 'Media']);
        Prioridad::create(['nombre_prioridad' => 'Alta']);
        Prioridad::create(['nombre_prioridad' => 'Crítica']);

        Categoria::create(['nombre_categoria' => 'Hardware']);
        Categoria::create(['nombre_categoria' => 'Software']);
        Categoria::create(['nombre_categoria' => 'Redes']);

        TipoEquipo::create(['nombre_tipo_equipo' => 'Laptop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Desktop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Impresora']);

        // --- 3. CREACIÓN DE PERSONAS ---
        $personaAdmin = Persona::create([
            'nombre' => 'Admin',
            'apellido' => 'General',
            'telefono' => '00000000',
            'id_oficina' => 1
        ]);

        $personaGestor = Persona::create([
            'nombre' => 'Gestor',
            'apellido' => 'De Soporte',
            'telefono' => '12345678',
            'id_oficina' => 1
        ]);

        $personaTecnico = Persona::create([
            'nombre' => 'Tecnico',
            'apellido' => 'General',
            'telefono' => '00000000',
            'id_oficina' => 1
        ]);

        $personaUsuario = Persona::create([
            'nombre' => 'usuario',
            'apellido' => 'Final',
            'telefono' => '00000000',
            'id_oficina' => 2
        ]);

        // --- 4. CREACIÓN DE USUARIOS Y ASIGNACIÓN DE ROLES ---
        
        // Crear Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@helpdesk.com',
            'role' => '1', // Tu columna manual
            'password' => Hash::make('admin123'),
            'id_persona' => $personaAdmin->id_persona,
        ]);
        // Asignar rol de Spatie
        $admin->assignRole($roleAdmin);

        $gestor = User::create([
            'name' => 'Gestor de Soporte',
            'email' => 'gestor@helpdesk.com',
            'role' => '2', // Siguiendo tu lógica de columna manual
            'password' => Hash::make('gestor123'),
            'id_persona' => $personaGestor->id_persona,
        ]);
        $gestor->assignRole($roleGestor);

        $admin = User::create([
            'name' => 'Tecnico',
            'email' => 'tecnico@helpdesk.com',
            'role' => '3', // Tu columna manual
            'password' => Hash::make('tecnico123'),
            'id_persona' => $personaTecnico->id_persona,
        ]);
        // Asignar rol de Spatie
        $admin->assignRole($roleTecnico);

        // Crear Usuario
        $usuarioFinal = User::create([
            'name' => 'Usuario GDC',
            'email' => 'usuario@helpdesk.com',
            'role' => '4',
            'password' => Hash::make('usuario123'),
            'id_persona' => $personaUsuario->id_persona,
        ]);
        $usuarioFinal->assignRole($roleUsuario);
    }
}