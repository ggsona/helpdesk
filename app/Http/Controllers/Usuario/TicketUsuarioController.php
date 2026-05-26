<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAdjunto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Categoria;
use App\Models\Prioridad;
use App\Models\TipoEquipo;
use App\Models\TicketComentario;

class TicketUsuarioController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where("id_usuario", Auth::id())->latest()->get();
        return view("usuario.tickets.index", compact("tickets"));
    }

    public function home()
    {
        return view("usuario.home");
    }

    public function create()
    {
        $categorias = Categoria::where("estado", true)->get(); // Solo categorías activas
        $prioridades = Prioridad::all();
        $tiposEquipo = TipoEquipo::all();
        $equiposAsignados = Auth::user()->equipos()->with('tipoEquipo')->where('estado', true)->get();
        
        return view("usuario.tickets.create", compact("categorias", "prioridades", "tiposEquipo", "equiposAsignados"));
    }

    public function store(Request $request)
    {
        // 1. Validar los datos
        $request->validate([
            "asunto" => "required|string|max:200",
            "descripcion_problema" => "required|string",
            "id_categoria" => "required|exists:categorias,id_categoria",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_equipo" => "nullable|exists:equipos,id_equipo",
            "adjuntos.*" => "nullable|file|max:10240",
        ]);

        // 2. Crear el Ticket como BORRADOR
        $categoria = Categoria::find($request->id_categoria);
        $ticket = Ticket::create([
            "id_usuario" => auth()->id(),
            "asunto" => $request->asunto,
            "id_categoria" => $request->id_categoria,
            "categoria_nombre_historico" => $categoria ? $categoria->nombre_categoria : null,
            "id_tipo_equipo" => $request->id_tipo_equipo,
            "id_equipo" => $request->id_equipo,
            "descripcion_problema" => $request->descripcion_problema,
            "id_prioridad" => null,
            "estatus" => 0,
        ]);

        // 3. Procesar Adjuntos
        if ($request->hasFile("adjuntos")) {
            foreach ($request->file("adjuntos") as $archivo) {
                $ruta = $archivo->store("tickets/". $ticket->id_ticket, "public");

                TicketAdjunto::create([
                    "id_ticket" => $ticket->id_ticket,
                    "ruta_archivo" => $ruta,
                    "nombre_original" => $archivo->getClientOriginalName(),
                    "tipo_mimo" => $archivo->getMimeType(),
                    "tamano" => $archivo->getSize(),
                ]);
            }
        }

        return redirect()->route("usuario.tickets.index")
            ->with("success", "Ticket guardado como borrador. Puedes revisarlo antes de enviarlo.");
    }

    public function enviar(Ticket $ticket)
    {
        if ($ticket->id_usuario !== auth()->id()) {
            abort(403);
        }

        $ticket->update(["estatus" => 1]);

        return back()->with("success", "¡Ticket enviado con éxito al equipo de soporte!");
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            "categoria", 
            "tecnico", 
            "prioridad", 
            "adjuntos", 
            "comentarios.usuario"
        ])
        ->where("id_usuario", auth()->id())
        ->findOrFail($id);

        return view("usuario.tickets.show", compact("ticket"));
    }

    public function edit($id)
    {
        $ticket = Ticket::where("id_usuario", auth()->id())
                        ->where("estatus", 0)
                        ->findOrFail($id);
                        
        $categorias = Categoria::where("estado", true)->get(); // Solo categorías activas
        return view("usuario.tickets.edit", compact("ticket", "categorias"));
    }

    public function destroy($id)
    {
        $ticket = Ticket::where("id_usuario", auth()->id())
                        ->where("estatus", 0)
                        ->findOrFail($id);
        
        $ticket->delete();

        return redirect()->route("usuario.tickets.index")
                        ->with("success", "Ticket eliminado correctamente.");
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::where("id_usuario", auth()->id())
                        ->where("estatus", 0)
                        ->findOrFail($id);

        $request->validate([
            "asunto" => "required|string|max:255",
            "descripcion_problema" => "nullable|string",
            "id_categoria" => "required|exists:categorias,id_categoria",
            "archivos.*" => "nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048",
        ]);

        $categoria = Categoria::find($request->id_categoria);
        $ticket->update([
            "asunto" => $request->asunto,
            "descripcion_problema" => $request->descripcion_problema,
            "id_categoria" => $request->id_categoria,
            "categoria_nombre_historico" => $categoria ? $categoria->nombre_categoria : null,
        ]);

        if ($request->hasFile("archivos")) {
            foreach ($request->file("archivos") as $file) {
                $nombreArchivo = time() . "_". $file->getClientOriginalName();
                $ruta = $file->storeAs("adjuntos_tickets", $nombreArchivo, "public");

                TicketAdjunto::create([
                    "id_ticket" => $ticket->id_ticket,
                    "ruta_archivo" => $ruta,
                    "nombre_original" => $file->getClientOriginalName(),
                    "tipo_mimo" => $file->getMimeType(),
                    "tamano" => $file->getSize(),
                ]);
            }
        }

        return redirect()->route("usuario.tickets.index")
                        ->with("success", "Borrador actualizado correctamente.");
    }

    public function storeComentario(Request $request, $id)
    {
        $request->validate([
            "mensaje" => "required|string|max:1000",
        ]);

        $ticket = Ticket::where("id_usuario", auth()->id())->findOrFail($id);

        TicketComentario::create([
            "id_ticket"  => $ticket->id_ticket,
            "id_usuario" => auth()->id(),
            "mensaje"    => $request->mensaje,
            "es_interno" => false,
        ]);

        return back()->with("success", "Mensaje enviado correctamente.");
    }
}