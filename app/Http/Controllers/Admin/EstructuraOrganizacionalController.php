<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NivelJerarquico;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;

class EstructuraOrganizacionalController extends Controller
{
    public function index()
    {
        // Traer niveles ordenados por su jerarquía
        $niveles = NivelJerarquico::orderBy('nivel', 'asc')->get();

        // Traer unidades raíz (parent_id = null) con todos sus hijos recursivos y su respectivo nivel
        $unidadesRaiz = UnidadAdministrativa::with(['children.nivel', 'nivel'])
            ->whereNull('parent_id')
            ->get();

        return view('admin.estructura.index', compact('niveles', 'unidadesRaiz'));
    }

    public function updateUnidad(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'id_nivel' => 'required|exists:niveles_jerarquicos,id',
            'parent_id' => 'nullable|exists:unidades_administrativas,id',
        ]);

        $unidad = UnidadAdministrativa::findOrFail($id);
        
        // Evitar que una unidad sea padre de sí misma
        if ($request->parent_id == $unidad->id) {
            return redirect()->back()->with('error', 'Una unidad no puede depender de sí misma.');
        }

        $unidad->update([
            'nombre' => $request->nombre,
            'id_nivel' => $request->id_nivel,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('admin.estructura.index')->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroyUnidad($id)
    {
        $unidad = UnidadAdministrativa::findOrFail($id);

        if ($unidad->children()->count() > 0) {
            return redirect()->back()->with('error', 'No puedes eliminar esta unidad porque tiene otras dependencias (hijos) asociadas a ella. Elimina primero los hijos.');
        }

        $unidad->delete();

        return redirect()->route('admin.estructura.index')->with('success', 'Unidad eliminada del organigrama.');
    }

    public function storeUnidad(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'id_nivel' => 'required|exists:niveles_jerarquicos,id',
            'parent_id' => 'nullable|exists:unidades_administrativas,id',
        ]);

        UnidadAdministrativa::create([
            'nombre' => $request->nombre,
            'id_nivel' => $request->id_nivel,
            'parent_id' => $request->parent_id,
            'is_active' => true
        ]);

        return redirect()->route('admin.estructura.index')->with('success', 'Unidad creada correctamente en el organigrama.');
    }
}
