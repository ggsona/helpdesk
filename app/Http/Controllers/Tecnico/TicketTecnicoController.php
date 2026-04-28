<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComentario;
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

        // 1. Obtener todos los tickets asignados al técnico (Estatus 2: En Gestión)
        $queryAsignados = Ticket::with(['prioridad', 'usuario.persona.oficina'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 2);

        // Agrupamos por prioridad para las listas que mencionaste
        $ticketsCriticos = (clone $queryAsignados)->where('id_prioridad', 4)->latest()->get(); // Crítica
        $ticketsAltos    = (clone $queryAsignados)->where('id_prioridad', 3)->latest()->get(); // Alta
        $ticketsMedios   = (clone $queryAsignados)->where('id_prioridad', 2)->latest()->get(); // Media
        $ticketsBajos    = (clone $queryAsignados)->where('id_prioridad', 1)->latest()->get(); // Baja

        // 2. Tickets ya solucionados por este técnico (Estatus 3: Resuelto)
        $ticketsResueltos = Ticket::with(['prioridad', 'usuario.persona.oficina'])
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
}