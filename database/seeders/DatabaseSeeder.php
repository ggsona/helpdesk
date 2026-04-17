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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Oficinas
        Oficina::create(['nombre_oficina' => 'Sede Central']);
        Oficina::create(['nombre_oficina' => 'Sucursal Norte']);

        // 2. Crear Prioridades
        Prioridad::create(['nombre_prioridad' => 'Baja']);
        Prioridad::create(['nombre_prioridad' => 'Media']);
        Prioridad::create(['nombre_prioridad' => 'Alta']);
        Prioridad::create(['nombre_prioridad' => 'Crítica']);

        // 3. Crear Categorías
        Categoria::create(['nombre_categoria' => 'Hardware']);
        Categoria::create(['nombre_categoria' => 'Software']);
        Categoria::create(['nombre_categoria' => 'Redes']);

        // 4. Crear Tipos de Equipo
        TipoEquipo::create(['nombre_tipo_equipo' => 'Laptop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Desktop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Impresora']);

        // 5. Crear la Persona para el Administrador
        $persona = Persona::create([
            'nombre' => 'Admin',
            'apellido' => 'General',
            'telefono' => '00000000',
            'id_oficina' => 1
        ]);

        // 6. Crear el Usuario Admin / Técnico inicial
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'), // Cambia esto luego
            'id_persona' => $persona->id_persona,
        ]);
    }
}