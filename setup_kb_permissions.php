<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$perms = ['ver-conocimiento', 'crear-articulo', 'editar-articulo', 'eliminar-articulo', 'gestionar-tags'];
foreach ($perms as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$rGestor = Role::where('name', 'Gestor')->first();
if($rGestor) $rGestor->givePermissionTo($perms);

$rTecnico = Role::where('name', 'Técnico')->first();
if($rTecnico) $rTecnico->givePermissionTo(['ver-conocimiento', 'crear-articulo', 'editar-articulo']);

echo "Permisos creados con exito.\n";
