<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComentario;
use App\Models\SolucionTecnica;
use App\Models\ArticuloConocimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'dificultad' => 'nullable|in:basica,intermedia,avanzada',
        ]);

        $ticket = Ticket::findOrFail($id);

        // 1. Guardar usando el modelo SolucionTecnica
        $solucion = SolucionTecnica::create([
            'id_ticket' => $id,
            'id_usuario_tecnico' => Auth::id(),
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
            'diagnostico' => $request->diagnostico,
            'causa_raiz' => $request->causa_raiz,
            'acciones_preventivas' => $request->acciones_preventivas,
            'tiempo_resolucion' => $request->tiempo_resolucion,
            'dificultad' => $request->dificultad ?? 'intermedia',
            'publicar_en_kb' => $request->has('publicar_en_kb'),
        ]);

        // 2. Cerrar el ticket
        $ticket->update([
            'estatus' => 3,
            'estado_tecnico' => 'resuelto',
            'fecha_cierre' => now(),
        ]);

        // 3. Crear artículo en KB si se solicitó
        if ($request->has('publicar_en_kb')) {
            $htmlContenido = "";
            if ($request->diagnostico) $htmlContenido .= "<h3>Diagnóstico</h3><p>{$request->diagnostico}</p>";
            if ($request->causa_raiz) $htmlContenido .= "<h3>Causa Raíz</h3><p>{$request->causa_raiz}</p>";
            $htmlContenido .= "<h3>Procedimiento Detallado</h3>" . $request->procedimiento_detallado;
            if ($request->acciones_preventivas) $htmlContenido .= "<h3>Acciones Preventivas</h3><p>{$request->acciones_preventivas}</p>";

            ArticuloConocimiento::create([
                'origen' => 'ticket',
                'id_solucion' => $solucion->id_solucion,
                'titulo' => $request->resumen_usuario,
                'slug' => Str::slug($request->resumen_usuario) . '-' . $solucion->id_solucion,
                'extracto' => Str::limit(strip_tags($request->procedimiento_detallado), 200),
                'contenido' => $htmlContenido,
                'id_categoria' => $ticket->id_categoria, // Hereda la categoría del ticket
                'id_autor' => Auth::id(),
                'estado' => 'publicado',
                'es_interno' => true, // Por defecto internos (para técnicos)
                'fecha_publicacion' => now(),
            ]);
        }

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
            'dificultad' => 'nullable|in:basica,intermedia,avanzada',
        ]);

        $ticket = Ticket::findOrFail($id);

        // Actualizamos el registro en la tabla soluciones_tecnicas
        $ticket->solucion->update([
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
            'diagnostico' => $request->diagnostico,
            'causa_raiz' => $request->causa_raiz,
            'acciones_preventivas' => $request->acciones_preventivas,
            'tiempo_resolucion' => $request->tiempo_resolucion,
            'dificultad' => $request->dificultad ?? 'intermedia',
            'publicar_en_kb' => $request->has('publicar_en_kb'),
        ]);

        if ($request->has('publicar_en_kb')) {
            $htmlContenido = "";
            if ($request->diagnostico) $htmlContenido .= "<h3>Diagnóstico</h3><p>{$request->diagnostico}</p>";
            if ($request->causa_raiz) $htmlContenido .= "<h3>Causa Raíz</h3><p>{$request->causa_raiz}</p>";
            $htmlContenido .= "<h3>Procedimiento Detallado</h3>" . $request->procedimiento_detallado;
            if ($request->acciones_preventivas) $htmlContenido .= "<h3>Acciones Preventivas</h3><p>{$request->acciones_preventivas}</p>";

            \App\Models\ArticuloConocimiento::updateOrCreate(
                ['id_solucion' => $ticket->solucion->id_solucion],
                [
                    'origen' => 'ticket',
                    'titulo' => $request->resumen_usuario,
                    'slug' => \Illuminate\Support\Str::slug($request->resumen_usuario) . '-' . $ticket->solucion->id_solucion,
                    'extracto' => \Illuminate\Support\Str::limit(strip_tags($request->procedimiento_detallado), 200),
                    'contenido' => $htmlContenido,
                    'id_categoria' => $ticket->id_categoria,
                    'id_autor' => \Illuminate\Support\Facades\Auth::id(),
                    'estado' => 'publicado',
                    'es_interno' => true,
                    'fecha_publicacion' => now(),
                ]
            );
        }

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