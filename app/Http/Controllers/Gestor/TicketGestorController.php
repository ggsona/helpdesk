<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComentario;
use App\Models\TicketAsignacion;
use Illuminate\Http\Request;
use App\Models\Prioridad;

class TicketGestorController extends Controller
{
    /**
     * Proteger el controlador con middleware de permisos
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:asignar-tickets']);
    }

    /**
     * Vista principal con las 3 listas (Pestañas)
     */
    public function index() 
    {
        // 1. Tickets por asignar (Estatus 1: Enviado / Pendiente)
        $ticketsNuevos = Ticket::with('prioridad')->where('estatus', 1)->latest()->get();

        // 2. Tickets en proceso (Estatus 2: Asignado / En Gestión)
        $ticketsAsignados = Ticket::with('prioridad', 'asignacion.tecnico')->where('estatus', 2)->latest()->get();

        // 3. Tickets resueltos (Estatus 3: Cerrado / Solucionado)
        $ticketsResueltos = Ticket::with('prioridad', 'solucion')->where('estatus', 3)->latest()->get();

        // 4. Lista de técnicos para poblar el SELECT del modal de asignación
        $tecnicos = User::role('tecnico')->get(); 

        // 5. Lista de prioridades para el SELECT del modal de asignación
        $prioridades = \App\Models\Prioridad::all(); 

        return view('soporte.tickets.index', compact(
            'ticketsNuevos', 
            'ticketsAsignados', 
            'ticketsResueltos', 
            'tecnicos',
            'prioridades'
        ));
    }

    /**
     * Procesa tanto la Asignación Inicial como la Reasignación
     */
    public function asignar(Request $request, $id)
    {
        $request->validate([
            'id_usuario_tecnico' => 'required|exists:users,id',
            'id_prioridad' => 'required|exists:prioridades,id_prioridad',
            'nota' => 'nullable|string'
        ]);

        $ticket = Ticket::findOrFail($id);
        
        // Verificamos si ya tiene una asignación previa para saber si es reasignación
        $esReasignacion = TicketAsignacion::where('id_ticket', $id)->exists();

        // 1. Guardar o actualizar la asignación técnica en ticket_asignaciones
        TicketAsignacion::updateOrCreate(
            ['id_ticket' => $id],
            [
                'id_usuario_tecnico' => $request->id_usuario_tecnico,
                'nota' => $request->nota, 
                'fecha_asignacion' => now()
            ]
        );

        // 2. Actualizar el ticket principal (Prioridad y Estatus)
        $ticket->update([
            'id_prioridad' => $request->id_prioridad,
            'estatus' => 2 // Siempre pasa a "En Proceso"
        ]);

        // 3. Generar comentario automático para el historial de mensajes
        $tecnicoNombre = User::find($request->id_usuario_tecnico)->name;
        
        if ($esReasignacion) {
            $mensaje = "🔄 **Reasignación**: El caso ha sido reasignado a " . $tecnicoNombre;
            $etiqueta = "Motivo";
        } else {
            $mensaje = "✅ **Asignación**: El caso ha sido asignado al técnico " . $tecnicoNombre;
            $etiqueta = "Sugerencia";
        }
        
        if ($request->nota) {
            $mensaje .= "\n\n**" . $etiqueta . "**: " . $request->nota;
        }

        TicketComentario::create([
            'id_ticket' => $id,
            'id_usuario' => auth()->id(),
            'mensaje' => $mensaje,
            'es_interno' => false 
        ]);

        return redirect()->back()->with('success', 'Técnico asignado correctamente.');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['usuario.persona.oficina', 'asignacion.tecnico'])->findOrFail($id);
        $tecnicos = User::role('tecnico')->get();
        $prioridades = \App\Models\Prioridad::all();

        return view('soporte.tickets.show', compact('ticket', 'tecnicos', 'prioridades'));
    }

    public function comentar(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000',
        ]);

        TicketComentario::create([
            'id_ticket'  => $id,
            'id_usuario' => auth()->id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => $request->has('es_interno'),
        ]);

        return back()->with('success', 'Mensaje enviado correctamente.');
    }
}