<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NivelJerarquico;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $niveles = NivelJerarquico::orderBy('nivel', 'asc')->get();
        
        // CANDADO: Revisamos si ya existe alguna unidad física para bloquear el arrastre
        $existenUnidades = UnidadAdministrativa::exists();

        return view('admin.configuraciones.index', compact('niveles', 'existenUnidades'));
    }

    public function reorderNiveles(Request $request)
    {
        // Doble validación de seguridad a nivel de backend: 
        if (UnidadAdministrativa::exists()) {
            return response()->json(['success' => false, 'message' => 'Seguridad: No se puede reordenar porque ya existen áreas creadas.'], 403);
        }

        $orden = $request->input('orden');
        
        if (is_array($orden)) {
            foreach ($orden as $indice => $id_nivel) {
                NivelJerarquico::where('id', $id_nivel)->update(['nivel' => $indice + 1]);
            }
            return response()->json(['success' => true, 'message' => 'Nomenclatura actualizada.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Formato inválido.'], 400);
    }

    public function toggleNivel(Request $request, $id)
    {
        $nivel = NivelJerarquico::findOrFail($id);
        
        // Podemos evitar que apaguen niveles si ya tienen unidades
        if ($nivel->unidades()->exists() && $nivel->is_active) {
            return response()->json(['success' => false, 'message' => 'No puedes desactivar un nivel que está en uso.'], 403);
        }

        $nivel->is_active = !$nivel->is_active;
        $nivel->save();

        return response()->json(['success' => true, 'is_active' => $nivel->is_active]);
    }

    public function storeNivel(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:niveles_jerarquicos,nombre'
        ]);

        // Lo colocamos automáticamente al final de la cola (último nivel)
        $ultimoNivel = NivelJerarquico::max('nivel');
        
        NivelJerarquico::create([
            'nombre' => $request->nombre,
            'nivel' => $ultimoNivel ? $ultimoNivel + 1 : 1,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Nueva nomenclatura agregada al catálogo.');
    }
}
