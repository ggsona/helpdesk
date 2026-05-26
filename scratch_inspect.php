<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cats = \App\Models\Categoria::all();
foreach ($cats as $c) {
    echo "ID: " . $c->id_categoria . " | Nombre: " . $c->nombre_categoria . " | Estado: " . ($c->estado ? 'Activa' : 'Inactiva') . "\n";
}
