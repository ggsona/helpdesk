<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-categorias");
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_marca' => 'required|string|max:100|unique:marcas,nombre_marca',
            'id_tipo_equipo' => 'nullable|exists:tipos_equipo,id_tipo_equipo'
        ]);

        Marca::create($request->all());

        return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'marcas'])
                         ->with('success', 'Marca creada exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);

            // Validación inteligente: verificar dependencias
            if ($marca->modelos()->exists()) {
                return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'marcas'])
                                 ->with('error', 'No se puede eliminar la marca porque tiene modelos asociados.');
            }

            $hasEquipos = \App\Models\Equipo::where('id_marca', $id)->exists();
            if ($hasEquipos) {
                return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'marcas'])
                                 ->with('error', 'No se puede eliminar la marca porque está en uso por activos registrados.');
            }

            $marca->delete();
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'marcas'])
                             ->with('success', 'Marca eliminada con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'marcas'])
                             ->with('error', 'No se puede eliminar la marca: ' . $e->getMessage());
        }
    }

    public function getModelos($id)
    {
        $marca = Marca::findOrFail($id);
        $modelos = $marca->modelos()->orderBy('nombre_modelo', 'asc')->get();
        return response()->json($modelos);
    }

    public function getMarcasPorTipo($id_tipo_equipo)
    {
        $marcas = Marca::where('id_tipo_equipo', $id_tipo_equipo)
                       ->orderBy('nombre_marca', 'asc')
                       ->get();
        return response()->json($marcas);
    }
}
