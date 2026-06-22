<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prioridad;
use App\Models\Ticket;
use App\Models\TicketAsignacion;
use App\Models\TicketComentario;

class GestorController extends Controller
{
    public function getOpcionesAsignacion()
    {
        $tecnicos = User::role('tecnico')->select('id', 'name', 'email')->get();
        $prioridades = Prioridad::all();

        return response()->json([
            'tecnicos' => $tecnicos,
            'prioridades' => $prioridades
        ]);
    }

    public function asignarTicket(Request $request, $id)
    {
        $request->validate([
            'id_usuario_tecnico' => 'required|exists:users,id',
            'id_prioridad' => 'required|exists:prioridades,id_prioridad',
            'nota' => 'nullable|string'
        ]);

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $esReasignacion = TicketAsignacion::where('id_ticket', $id)->exists();

        TicketAsignacion::updateOrCreate(
            ['id_ticket' => $id],
            [
                'id_usuario_tecnico' => $request->id_usuario_tecnico,
                'nota' => $request->nota, 
                'fecha_asignacion' => now()
            ]
        );

        $ticket->update([
            'id_prioridad' => $request->id_prioridad,
            'estatus' => 2, // En Proceso
            'estado_tecnico' => 'pendiente',
        ]);

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

        return response()->json([
            'success' => true,
            'message' => 'Técnico asignado correctamente.'
        ]);
    }
}
