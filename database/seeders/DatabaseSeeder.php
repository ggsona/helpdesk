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
        $roleAdmin   = Role::create(['name' => 'admin']);
        $roleTecnico = Role::create(['name' => 'tecnico']);
        $roleCliente = Role::create(['name' => 'cliente']);

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

        $personaTecnico = Persona::create([
            'nombre' => 'Tecnico',
            'apellido' => 'General',
            'telefono' => '00000000',
            'id_oficina' => 1
        ]);

        $personaCliente = Persona::create([
            'nombre' => 'Cliente',
            'apellido' => 'General',
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

        $admin = User::create([
            'name' => 'Tecnico',
            'email' => 'tecnico@helpdesk.com',
            'role' => '2', // Tu columna manual
            'password' => Hash::make('tecnico123'),
            'id_persona' => $personaTecnico->id_persona,
        ]);
        // Asignar rol de Spatie
        $admin->assignRole($roleTecnico);

        // Crear Cliente
        $cliente = User::create([
            'name' => 'Cliente',
            'email' => 'cliente@helpdesk.com',
            'role' => '3', // Tu columna manual
            'password' => Hash::make('cliente123'),
            'id_persona' => $personaCliente->id_persona,
        ]);
        // Asignar rol de Spatie
        $cliente->assignRole($roleCliente);
    }
}