<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Ticket; // Asegúrate de tener el modelo Ticket
use App\Models\User;
use Illuminate\Http\Request;

class TicketGestorController extends Controller
{
    // Vista principal: Todos los tickets entrantes
    public function index()
    {
        // Traemos los tickets con sus relaciones para no hacer muchas consultas
        $tickets = Ticket::with(['cliente', 'tecnico', 'categoria'])->orderBy('created_at', 'desc')->get();
        
        // Traemos solo a los usuarios que tengan el rol de tecnico para el modal de asignación
        $tecnicos = User::role('tecnico')->get();

        return view('gestor.tickets.index', compact('tickets', 'tecnicos'));
    }

    // Lógica para asignar el técnico
    public function asignar(Request $request, $id)
    {
        $request->validate([
            'id_tecnico' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->id_tecnico = $request->id_tecnico;
        $ticket->estado = 'asignado'; // Cambiamos el estado automáticamente
        $ticket->save();

        return back()->with('success', 'Ticket asignado correctamente al técnico.');
    }
}