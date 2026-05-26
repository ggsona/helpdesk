<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ["nombre_categoria" => "Problemas de Software", "estado" => true],
            ["nombre_categoria" => "Problemas de Hardware", "estado" => true],
            ["nombre_categoria" => "Problemas de Red", "estado" => true],
            ["nombre_categoria" => "Acceso y Permisos", "estado" => true],
            ["nombre_categoria" => "Correo Electrónico", "estado" => true],
            ["nombre_categoria" => "Impresoras y Escáneres", "estado" => true],
            ["nombre_categoria" => "Telefonía", "estado" => true],
            ["nombre_categoria" => "Aplicaciones Internas", "estado" => true],
            ["nombre_categoria" => "Soporte para Nuevos Ingresos", "estado" => true],
            ["nombre_categoria" => "Capacitación y Manuales", "estado" => true],
            ["nombre_categoria" => "Mantenimiento Preventivo", "estado" => true],
            ["nombre_categoria" => "Problemas de Seguridad", "estado" => false], // Categoría inactiva de ejemplo
        ];

        foreach ($categorias as $categoriaData) {
            Categoria::updateOrCreate(
                ['nombre_categoria' => $categoriaData['nombre_categoria']],
                ['estado' => $categoriaData['estado']]
            );
        }
    }
}
