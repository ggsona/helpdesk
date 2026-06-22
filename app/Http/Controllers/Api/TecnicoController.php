<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketComentario;
use App\Models\SolucionTecnica;
use App\Models\ArticuloConocimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TecnicoController extends Controller
{
    public function comentar(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000'
        ]);

        TicketComentario::create([
            'id_ticket'  => $id,
            'id_usuario' => Auth::id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => $request->has('es_interno') ? $request->es_interno : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario agregado.'
        ]);
    }

    public function resolverTicket(Request $request, $id)
    {
        $request->validate([
            'resumen_usuario' => 'required|string|max:255',
            'procedimiento_detallado' => 'required|string',
            'dificultad' => 'nullable|in:basica,intermedia,avanzada',
        ]);

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket no encontrado'], 404);
        }

        if ($ticket->estatus == 3) {
            return response()->json(['success' => false, 'message' => 'Este ticket ya fue resuelto.'], 400);
        }

        // Guardar Solución Técnica
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
            'publicar_en_kb' => $request->has('publicar_en_kb') ? $request->publicar_en_kb : false,
        ]);

        // Cerrar el ticket
        $ticket->update([
            'estatus' => 3,
            'estado_tecnico' => 'resuelto',
            'fecha_cierre' => now(),
        ]);

        // Crear artículo en KB si se solicitó
        if ($request->has('publicar_en_kb') && $request->publicar_en_kb) {
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
                'id_categoria' => $ticket->id_categoria, 
                'id_autor' => Auth::id(),
                'estado' => 'publicado',
                'es_interno' => true,
                'fecha_publicacion' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket resuelto y cerrado correctamente.'
        ]);
    }
}
