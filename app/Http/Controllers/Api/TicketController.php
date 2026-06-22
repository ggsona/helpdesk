<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Si es usuario estándar, ve sus propios tickets creados
        if ($user->hasRole('usuario')) {
            $tickets = Ticket::with(['categoria', 'prioridad', 'adjuntos', 'tecnico', 'equipo', 'solucion', 'comentarios.usuario', 'usuario.persona.unidadAdministrativa'])
                ->where('id_usuario', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $tickets->each->append('estado_texto');
            return response()->json($tickets);
        }
        
        // Si es técnico, ve los tickets que tiene asignados
        if ($user->hasRole('tecnico')) {
            $tickets = Ticket::with(['categoria', 'prioridad', 'usuario.persona.unidadAdministrativa', 'adjuntos', 'tecnico', 'equipo', 'solucion', 'comentarios.usuario'])
                ->whereHas('asignacion', function ($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            $tickets->each->append('estado_texto');
            return response()->json($tickets);
        }

        // Si es admin o gestor, ve todos los tickets
        if ($user->hasRole('admin') || $user->hasRole('gestor')) {
            $tickets = Ticket::with(['categoria', 'prioridad', 'usuario.persona.unidadAdministrativa', 'adjuntos', 'tecnico', 'equipo', 'solucion', 'comentarios.usuario'])
                ->orderBy('created_at', 'desc')
                ->get();
            $tickets->each->append('estado_texto');
            return response()->json($tickets);
        }

        return response()->json(['message' => 'Rol no soportado en la App'], 403);
    }

    public function getFormOptions()
    {
        $categorias = \App\Models\Categoria::where('estado', true)->get();
        $tiposEquipo = \App\Models\TipoEquipo::all();
        $equipos = Auth::user()->equipos()->with('tipoEquipo')->where('estado', true)->get();
        
        return response()->json([
            'success' => true,
            'categorias' => $categorias,
            'tipos_equipo' => $tiposEquipo,
            'equipos' => $equipos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "asunto" => "required|string|max:200",
            "descripcion_problema" => "required|string",
            "id_categoria" => "required|exists:categorias,id_categoria",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_equipo" => "nullable|exists:equipos,id_equipo",
            "adjuntos" => "nullable|array",
            "adjuntos.*" => "image|max:10240", // Hasta 10MB por imagen
        ]);

        $categoria = \App\Models\Categoria::find($request->id_categoria);
        
        $ticket = Ticket::create([
            "id_usuario" => Auth::id(),
            "asunto" => $request->asunto,
            "id_categoria" => $request->id_categoria,
            "categoria_nombre_historico" => $categoria ? $categoria->nombre_categoria : null,
            "id_tipo_equipo" => $request->id_tipo_equipo,
            "id_equipo" => $request->id_equipo,
            "descripcion_problema" => $request->descripcion_problema,
            "id_prioridad" => null,
            "estatus" => 1,
        ]);

        if ($request->hasFile("adjuntos")) {
            foreach ($request->file("adjuntos") as $archivo) {
                $ruta = $archivo->store("tickets/". $ticket->id_ticket, "public");

                \App\Models\TicketAdjunto::create([
                    "id_ticket" => $ticket->id_ticket,
                    "ruta_archivo" => $ruta,
                    "nombre_original" => $archivo->getClientOriginalName(),
                    "tipo_mimo" => $archivo->getMimeType(),
                    "tamano" => $archivo->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado con éxito',
            'ticket' => $ticket
        ], 201);
    }
}
