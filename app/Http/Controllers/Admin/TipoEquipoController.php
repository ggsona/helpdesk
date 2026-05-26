<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoEquipo;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Http\Request;

class TipoEquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-equipos");
    }

    public function index(Request $request)
    {
        // 1. Tipos de Equipos paginados
        $tiposQuery = TipoEquipo::query();
        if ($request->filled('search_tipo')) {
            $tiposQuery->where('nombre_tipo_equipo', 'like', '%' . $request->search_tipo . '%');
        }
        $tipos = $tiposQuery->orderBy('nombre_tipo_equipo', 'asc')->paginate(10, ['*'], 'tipos_page');

        // 2. Marcas paginadas
        $marcasQuery = Marca::with('tipoEquipo');
        if ($request->filled('search_marca')) {
            $marcasQuery->where('nombre_marca', 'like', '%' . $request->search_marca . '%');
        }
        $marcas = $marcasQuery->orderBy('nombre_marca', 'asc')->paginate(10, ['*'], 'marcas_page');

        // 3. Modelos paginados
        $modelosQuery = Modelo::with('marca');
        if ($request->filled('search_modelo')) {
            $modelosQuery->where('nombre_modelo', 'like', '%' . $request->search_modelo . '%');
        }
        $modelos = $modelosQuery->orderBy('nombre_modelo', 'asc')->paginate(10, ['*'], 'modelos_page');

        // Todos los tipos para los selectores
        $todosTipos = TipoEquipo::orderBy('nombre_tipo_equipo', 'asc')->get();
        // Todas las marcas para los selectores
        $todasMarcas = Marca::orderBy('nombre_marca', 'asc')->get();

        return view('admin.equipos.catalogos.index', compact('tipos', 'marcas', 'modelos', 'todosTipos', 'todasMarcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_tipo_equipo' => 'required|string|max:100|unique:tipos_equipo,nombre_tipo_equipo'
        ]);

        TipoEquipo::create($request->all());

        return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'tipos'])
                         ->with('success', 'Tipo de equipo creado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $tipo = TipoEquipo::findOrFail($id);

            // Validación inteligente: No permitir borrar si tiene marcas o equipos
            if ($tipo->marcas()->exists()) {
                return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'tipos'])
                                 ->with('error', 'No se puede eliminar el tipo de equipo porque tiene marcas asociadas.');
            }

            // Comprobar si hay equipos que usan este tipo
            $hasEquipos = \App\Models\Equipo::where('id_tipo_equipo', $id)->exists();
            if ($hasEquipos) {
                return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'tipos'])
                                 ->with('error', 'No se puede eliminar el tipo de equipo porque hay activos registrados en el inventario bajo esta categoría.');
            }

            $tipo->delete();
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'tipos'])
                             ->with('success', 'Tipo de equipo eliminado con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('admin.equipos.catalogos.index', ['tab' => 'tipos'])
                             ->with('error', 'Error al eliminar el tipo de equipo: ' . $e->getMessage());
        }
    }
}
