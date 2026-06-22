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
            $tickets = Ticket::with(['categoria', 'prioridad', 'estado'])
                ->where('id_usuario_creador', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($tickets);
        }
        
        // Si es técnico, ve los tickets que tiene asignados
        if ($user->hasRole('tecnico')) {
            $tickets = Ticket::with(['categoria', 'prioridad', 'estado', 'creador'])
                ->whereHas('asignaciones', function($query) use ($user) {
                    $query->where('id_usuario_tecnico', $user->id)
                          ->where('activo', true); // Asumiendo que solo se ven los activos/actuales
                })
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($tickets);
        }

        return response()->json(['message' => 'Rol no soportado en la App'], 403);
    }

    public function store(Request $request)
    {
        // Esta lógica se ampliará luego con carga de imágenes y más detalles.
        // Solo un esquema básico para iniciar.
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'id_categoria' => 'required|exists:categorias,id_categoria'
        ]);

        $ticket = Ticket::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'id_categoria' => $request->id_categoria,
            'id_usuario_creador' => Auth::id(),
            'id_estado' => 1, // Nuevo / Por Asignar
            'id_prioridad' => 1, // Prioridad base
            'codigo_ticket' => 'TKT-' . strtoupper(uniqid()), // Simplificado por ahora
        ]);

        return response()->json([
            'message' => 'Ticket creado con éxito',
            'ticket' => $ticket
        ], 201);
    }
}
