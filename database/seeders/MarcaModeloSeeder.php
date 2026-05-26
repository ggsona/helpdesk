<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\TipoEquipo;

class MarcaModeloSeeder extends Seeder
{
    public function run(): void
    {
        $laptop = TipoEquipo::where('nombre_tipo_equipo', 'Laptop')->first();
        $desktop = TipoEquipo::where('nombre_tipo_equipo', 'Desktop')->first();
        $servidor = TipoEquipo::where('nombre_tipo_equipo', 'Servidor')->first();
        $impresora = TipoEquipo::where('nombre_tipo_equipo', 'Impresora')->first();
        $teclado = TipoEquipo::where('nombre_tipo_equipo', 'Teclado')->first();
        $mouse = TipoEquipo::where('nombre_tipo_equipo', 'Mouse')->first();
        $monitor = TipoEquipo::where('nombre_tipo_equipo', 'Monitor')->first();
        $switch = TipoEquipo::where('nombre_tipo_equipo', 'Switch')->first();
        $router = TipoEquipo::where('nombre_tipo_equipo', 'Router')->first();
        $ups = TipoEquipo::where('nombre_tipo_equipo', 'UPS')->first();
        $telIp = TipoEquipo::where('nombre_tipo_equipo', 'Teléfono IP')->first();
        $tarjetaGrafica = TipoEquipo::where('nombre_tipo_equipo', 'Tarjeta Gráfica')->first();

        $marcas = [
            'Dell' => [
                'tipo' => $laptop ? $laptop->id_tipo_equipo : null,
                'modelos' => ['Latitude 5420', 'Latitude 3420', 'Inspiron 15', 'Precision 3560']
            ],
            'Lenovo' => [
                'tipo' => $desktop ? $desktop->id_tipo_equipo : null,
                'modelos' => ['ThinkCentre M70q', 'ThinkCentre M90', 'IdeaCentre 3', 'ThinkStation P340']
            ],
            'HP' => [
                'tipo' => $impresora ? $impresora->id_tipo_equipo : null,
                'modelos' => ['LaserJet Pro M404dn', 'LaserJet Tank 1504', 'Ink Tank 415', 'Neverstop Laser 1000w']
            ],
            'Logitech' => [
                'tipo' => $mouse ? $mouse->id_tipo_equipo : null,
                'modelos' => ['MX Master 3S', 'G Pro X Superlight', 'M170 Wireless', 'Pebble M350']
            ],
            'Cisco' => [
                'tipo' => $switch ? $switch->id_tipo_equipo : null,
                'modelos' => ['Catalyst 2960-L', 'SG350X-24', 'Catalyst 9300', 'CBS250-24T']
            ],
            'Apple' => [
                'tipo' => $laptop ? $laptop->id_tipo_equipo : null,
                'modelos' => ['MacBook Pro M3', 'MacBook Air M2', 'MacBook Pro 16"']
            ],
            'Samsung' => [
                'tipo' => $monitor ? $monitor->id_tipo_equipo : null,
                'modelos' => ['Odyssey G5 27"', 'SyncMaster 24"', 'Curved Monitor 32"', 'Smart Monitor M7']
            ],
            'NVIDIA' => [
                'tipo' => $tarjetaGrafica ? $tarjetaGrafica->id_tipo_equipo : null,
                'modelos' => ['GeForce RTX 3050', 'GeForce RTX 4060', 'GeForce RTX 4070', 'GeForce GTX 1660 Super']
            ],
            'APC' => [
                'tipo' => $ups ? $ups->id_tipo_equipo : null,
                'modelos' => ['Smart-UPS 1500VA', 'Back-UPS 800VA', 'Easy-UPS 700VA']
            ],
            'Yealink' => [
                'tipo' => $telIp ? $telIp->id_tipo_equipo : null,
                'modelos' => ['SIP-T31G', 'SIP-T46U', 'SIP-T54W']
            ],
            'Corsair' => [
                'tipo' => $teclado ? $teclado->id_tipo_equipo : null,
                'modelos' => ['K55 RGB PRO', 'K70 RGB MK.2', 'K65 RGB MINI']
            ],
            'Huawei' => [
                'tipo' => $router ? $router->id_tipo_equipo : null,
                'modelos' => ['AR6121 Router', 'NetEngine AR617', 'WiFi AX3']
            ],
            'Supermicro' => [
                'tipo' => $servidor ? $servidor->id_tipo_equipo : null,
                'modelos' => ['SuperServer 1029P', 'SuperServer 6029P', 'AS-1114S-WN10RT']
            ]
        ];

        foreach ($marcas as $nombreMarca => $info) {
            $marca = Marca::updateOrCreate(
                ['nombre_marca' => $nombreMarca],
                ['id_tipo_equipo' => $info['tipo']]
            );
            
            foreach ($info['modelos'] as $nombreModelo) {
                Modelo::updateOrCreate(
                    ['nombre_modelo' => $nombreModelo, 'id_marca' => $marca->id_marca],
                    []
                );
            }
        }
    }
}
