<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-categorias");
    }

    public function index(Request $request)
    {
        $query = Categoria::query();

        // Búsqueda por nombre de categoría
        if ($request->filled("search")) {
            $query->where("nombre_categoria", "like", "%". $request->search ."%");
        }

        // Filtro por estado (activo/inactivo)
        if ($request->filled("estado")) {
            $query->where("estado", $request->estado == "activo" ? true : false);
        } else {
            // Por defecto, mostrar solo categorías activas
            $query->where("estado", true);
        }

        $categorias = $query->with(['creator', 'updater'])->paginate(10); // Paginación de 10 categorías por página

        // Cargar marcas y modelos
        $marcas = Marca::with('tipoEquipo')->orderBy('nombre_marca', 'asc')->get();
        $modelos = Modelo::with('marca')->orderBy('nombre_modelo', 'asc')->paginate(10, ['*'], 'modelos_page');
        $tiposEquipo = \App\Models\TipoEquipo::orderBy('nombre_tipo_equipo', 'asc')->get();

        if ($request->ajax() && !$request->has('modelos_page')) {
            return view("admin.categorias._categorias_table", compact("categorias"))->render();
        }

        return view("admin.categorias.index", compact("categorias", "marcas", "modelos", "tiposEquipo"));
    }

    public function create()
    {
        return view("admin.categorias.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre_categoria" => "required|string|max:100|unique:categorias",
        ]);

        $categoria = Categoria::create(array_merge($request->all(), ['created_by' => auth()->id()]));

        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría creada exitosamente.']);
        }

        return redirect()->route("admin.categorias.index")
                         ->with("success", "Categoría creada exitosamente.");
    }

    public function edit(Categoria $categoria)
    {
        return view("admin.categorias.edit", compact("categoria"));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            "nombre_categoria" => [
                "required",
                "string",
                "max:100",
                \Illuminate\Validation\Rule::unique('categorias', 'nombre_categoria')->ignore($categoria->id_categoria, 'id_categoria')
            ],
            "estado" => "required|in:0,1",
        ]);

        $categoria->update(array_merge($request->all(), ["updated_by" => auth()->id()]));

        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría actualizada exitosamente.']);
        }

        return redirect()->route("admin.categorias.index")
                         ->with("success", "Categoría actualizada exitosamente.");
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        try {
            $categoria->update(["estado" => false, "updated_by" => auth()->id()]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría desactivada exitosamente.']);
        }

            if ($request->ajax()) {
                return response()->json(['success' => 'Categoría desactivada exitosamente.']);
            }

            return redirect()->route("admin.categorias.index")
                            ->with("success", "Categoría desactivada exitosamente.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Hubo un error al desactivar la categoría: ' . $e->getMessage()], 500);
            }
            return redirect()->route("admin.categorias.index")
                            ->with("error", "Hubo un error al desactivar la categoría: " . $e->getMessage());
        }
    }

    public function activate(Categoria $categoria)
    {
        $categoria->update(["estado" => true, "updated_by" => auth()->id()]);
        
        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría activada exitosamente.']);
        }

        return redirect()->route("admin.categorias.index")
                         ->with("success", "Categoría activada exitosamente.");
    }
}
