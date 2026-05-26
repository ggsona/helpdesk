<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modelo;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-categorias");
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_modelo' => 'required|string|max:100',
            'id_marca' => 'required|exists:marcas,id_marca'
        ]);

        // Evitar duplicados de modelo por marca
        $exists = Modelo::where('nombre_modelo', $request->nombre_modelo)
                        ->where('id_marca', $request->id_marca)
                        ->exists();

        if ($exists) {
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'modelos'])
                             ->with('error', 'El modelo ya existe para esta marca.');
        }

        Modelo::create($request->all());

        return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'modelos'])
                         ->with('success', 'Modelo creado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $modelo = Modelo::findOrFail($id);

            // Validación inteligente
            $hasEquipos = \App\Models\Equipo::where('id_modelo', $id)->exists();
            if ($hasEquipos) {
                return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'modelos'])
                                 ->with('error', 'No se puede eliminar el modelo porque está en uso por activos en el inventario.');
            }

            $modelo->delete();
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'modelos'])
                             ->with('success', 'Modelo eliminado con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'modelos'])
                             ->with('error', 'No se puede eliminar el modelo: ' . $e->getMessage());
        }
    }
}
