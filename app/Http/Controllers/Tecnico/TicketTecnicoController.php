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
     * Vista principal con Tabs (Pills) para Asignados y Resueltos.
     */
    public function index() 
    {
        $usuarioId = Auth::id();

        // 1. Tickets asignados (se mantiene igual por ahora)
        $queryAsignados = Ticket::with(['prioridad', 'usuario.persona.oficina'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 2);

        $ticketsCriticos = (clone $queryAsignados)->where('id_prioridad', 4)->latest()->get();
        $ticketsAltos    = (clone $queryAsignados)->where('id_prioridad', 3)->latest()->get();
        $ticketsMedios   = (clone $queryAsignados)->where('id_prioridad', 2)->latest()->get();
        $ticketsBajos    = (clone $queryAsignados)->where('id_prioridad', 1)->latest()->get();

        // 2. ACTUALIZACIÓN AQUÍ: Agregamos 'solucion' a la carga
        $ticketsResueltos = Ticket::with(['prioridad', 'usuario.persona.oficina', 'solucion']) // <--- Agregado 'solucion'
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 3)
            ->latest()
            ->get();

        return view('tecnico.tickets.index', compact(
            'ticketsCriticos', 
            'ticketsAltos', 
            'ticketsMedios', 
            'ticketsBajos', 
            'ticketsResueltos'
        ));
    }

    /**
     * Ver el detalle del ticket
     */
    public function show($id)
    {
        $usuarioId = Auth::id();

        $ticket = Ticket::with(['usuario.persona.oficina', 'asignacion.tecnico', 'comentarios.usuario', 'prioridad'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->findOrFail($id);

        return view('tecnico.tickets.show', compact('ticket'));
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
            return redirect()->route('tecnico.tickets.index')
                             ->with('info', 'Este ticket ya fue resuelto.');
        }

        return view('tecnico.tickets.resolver', compact('ticket'));
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
            'fecha_cierre' => now(),
        ]);

        return redirect()->route('tecnico.tickets.index')
                         ->with('success', 'Solución publicada y ticket cerrado correctamente.');
    }
}