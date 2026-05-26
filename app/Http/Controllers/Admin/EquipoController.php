<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\User;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-equipos");
    }

    public function index(Request $request)
    {
        $query = Equipo::query();

        // Búsqueda por nombre o número de bien
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("nombre", "like", "%" . $search . "%")
                  ->orWhere("numero_bien", "like", "%" . $search . "%");
            });
        }

        // Filtro por tipo de equipo
        if ($request->filled("id_tipo_equipo")) {
            $query->where("id_tipo_equipo", $request->id_tipo_equipo);
        }

        // Filtro por estado
        if ($request->filled("estado")) {
            $query->where("estado", $request->estado == "activo" ? true : false);
        }

        $equipos = $query->with(['tipoEquipo', 'usuarioAsignado', 'marca', 'modelo'])->paginate(10);
        $tipos = TipoEquipo::all();

        if ($request->ajax()) {
            return view("admin.equipos._equipos_table", compact("equipos"))->render();
        }

        return view("admin.equipos.index", compact("equipos", "tipos"));
    }

    public function create()
    {
        $tipos = TipoEquipo::all();
        $usuarios = User::where('is_approved', true)->with('persona')->get();
        $marcas = Marca::orderBy('nombre_marca', 'asc')->get();
        return view("admin.equipos.create", compact("tipos", "usuarios", "marcas"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required|string|max:150",
            "numero_bien" => "nullable|string|max:100|unique:equipos,numero_bien",
            "id_marca" => "nullable|exists:marcas,id_marca",
            "id_modelo" => "nullable|exists:modelos,id_modelo",
            "ip_address" => "nullable|ip",
            "mac_address" => "nullable|string|max:50",
            "ram" => "nullable|string|max:100",
            "procesador" => "nullable|string|max:100",
            "disco_duro" => "nullable|string|max:100",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_usuario_asignado" => "nullable|exists:users,id",
            "estado" => "required|boolean",
        ]);

        Equipo::create($request->all());

        return redirect()->route("admin.equipos.index")
                         ->with("success", "Equipo registrado exitosamente.");
    }

    public function edit(Equipo $equipo)
    {
        $tipos = TipoEquipo::all();
        $usuarios = User::where('is_approved', true)->with('persona')->get();
        $marcas = Marca::orderBy('nombre_marca', 'asc')->get();
        
        // Cargar modelos de la marca del equipo si ya tiene una marca seleccionada
        $modelos = [];
        if ($equipo->id_marca) {
            $modelos = Modelo::where('id_marca', $equipo->id_marca)->orderBy('nombre_modelo', 'asc')->get();
        }
        
        return view("admin.equipos.edit", compact("equipo", "tipos", "usuarios", "marcas", "modelos"));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $request->validate([
            "nombre" => "required|string|max:150",
            "numero_bien" => "nullable|string|max:100|unique:equipos,numero_bien," . $equipo->id_equipo . ",id_equipo",
            "id_marca" => "nullable|exists:marcas,id_marca",
            "id_modelo" => "nullable|exists:modelos,id_modelo",
            "ip_address" => "nullable|ip",
            "mac_address" => "nullable|string|max:50",
            "ram" => "nullable|string|max:100",
            "procesador" => "nullable|string|max:100",
            "disco_duro" => "nullable|string|max:100",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_usuario_asignado" => "nullable|exists:users,id",
            "estado" => "required|boolean",
        ]);

        $equipo->update($request->all());

        return redirect()->route("admin.equipos.index")
                         ->with("success", "Equipo actualizado exitosamente.");
    }

    public function destroy(Equipo $equipo)
    {
        try {
            $equipo->delete();
            return redirect()->route("admin.equipos.index")
                             ->with("success", "Equipo eliminado del inventario.");
        } catch (\Exception $e) {
            return redirect()->route("admin.equipos.index")
                             ->with("error", "No se puede eliminar el equipo porque está asociado a registros históricos.");
        }
    }
}
