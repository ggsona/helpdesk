<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoEquipo;

class TipoEquipoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Laptop',
            'Desktop',
            'Servidor',
            'Impresora',
            'Teclado',
            'Mouse',
            'Monitor',
            'Switch',
            'Router',
            'UPS',
            'Teléfono IP',
            'Tarjeta Gráfica'
        ];

        foreach ($tipos as $tipo) {
            TipoEquipo::firstOrCreate(['nombre_tipo_equipo' => $tipo]);
        }
    }
}
