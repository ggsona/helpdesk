<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComentario;
use App\Models\SolucionTecnica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketTecnicoController extends Controller
{
    /**
     * Proteger el controlador con middleware de permisos
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:resolver-tickets']);
    }

    /**
     * Vista principal del tablero Kanban para técnicos.
     */
    public function index() 
    {
        $usuarioId = Auth::id();

        $queryKanban = Ticket::with(['prioridad', 'usuario.persona.unidadAdministrativa', 'categoria', 'tipoEquipo'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            });

        $ticketsPendientes = (clone $queryKanban)
            ->where('estatus', 2)
            ->where('estado_tecnico', 'pendiente')
            ->latest()
            ->get();

        $ticketsEnProgreso = (clone $queryKanban)
            ->where('estatus', 2)
            ->where('estado_tecnico', 'en_progreso')
            ->latest()
            ->get();

        $ticketsResueltos = Ticket::with(['prioridad', 'usuario.persona.unidadAdministrativa', 'solucion'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 3)
            ->latest()
            ->get();

        return view('soporte.tickets.index', compact(
            'ticketsPendientes',
            'ticketsEnProgreso',
            'ticketsResueltos'
        ));
    }

    /**
     * Ver el detalle del ticket
     */
    public function show($id)
    {
        $usuarioId = Auth::id();

        $ticket = Ticket::with(['usuario.persona.unidadAdministrativa', 'asignacion.tecnico', 'comentarios.usuario', 'prioridad'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->findOrFail($id);

        return view('soporte.tickets.show', compact('ticket'));
    }

    /**
     * Enviar mensaje (público o interno)
     */
    public function comentar(Request $request, $id)
    {
        $request->validate(['mensaje' => 'required|string|max:1000']);

        TicketComentario::create([
            'id_ticket'  => $id,
            'id_usuario' => Auth::id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => $request->has('es_interno'),
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }

    public function crearSolucion($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Seguridad: si ya está resuelto, mandarlo de vuelta
        if ($ticket->estatus == 3) {
            return redirect()->route('soporte.tickets.tecnico.index')
                             ->with('info', 'Este ticket ya fue resuelto.');
        }

        return view('soporte.tickets.resolver', compact('ticket'));
    }

    public function guardarSolucion(Request $request, $id)
    {
        $request->validate([
            'resumen_usuario' => 'required|string|max:255',
            'procedimiento_detallado' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        // 1. Guardar usando el modelo SolucionTecnica
        SolucionTecnica::create([
            'id_ticket' => $id,
            'id_usuario_tecnico' => Auth::id(),
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
        ]);

        // 2. Cerrar el ticket
        $ticket->update([
            'estatus' => 3,
            'estado_tecnico' => 'resuelto',
            'fecha_cierre' => now(),
        ]);

        return redirect()->route('soporte.tickets.tecnico.index')
                         ->with('success', 'Solución publicada y ticket cerrado correctamente.');
    }

    public function editarSolucion($id)
    {
        // Buscamos el ticket con su solución
        $ticket = Ticket::with('solucion')->findOrFail($id);

        // Verificamos que tenga una solución que editar
        if (!$ticket->solucion) {
            return redirect()->route('soporte.tickets.tecnico.resolver', $id);
        }

        return view('soporte.tickets.editar_solucion', compact('ticket'));
    }

    /**
     * Actualiza los datos en la tabla soluciones_tecnicas
     */
    public function actualizarSolucion(Request $request, $id)
    {
        $request->validate([
            'resumen_usuario' => 'required|string|max:255',
            'procedimiento_detallado' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        // Actualizamos el registro en la tabla soluciones_tecnicas
        $ticket->solucion->update([
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
        ]);

        return redirect()->route('soporte.tickets.tecnico.index')
                        ->with('success', 'La solución técnica ha sido actualizada.');
    }

    public function actualizarEstadoKanban(Request $request, $id)
    {
        $request->validate([
            'estado_tecnico' => 'required|in:pendiente,en_progreso',
        ]);

        $ticket = Ticket::where('id_ticket', $id)
            ->where('estatus', 2)
            ->whereHas('asignacion', function ($q) {
                $q->where('id_usuario_tecnico', Auth::id());
            })
            ->firstOrFail();

        $ticket->update([
            'estado_tecnico' => $request->estado_tecnico,
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Estado actualizado correctamente.',
        ]);
    }
}