<?php

namespace App\Http\Controllers\Cliente; // <-- Fíjate en el \Cliente

use App\Http\Controllers\Controller; // <-- IMPORTANTE: Debes importar el controlador base
use App\Models\Ticket;
use App\Models\TicketAdjunto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Categoria;
use App\Models\Prioridad;
use App\Models\TipoEquipo;

class TicketClienteController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('id_usuario', Auth::id())->latest()->get();
        return view('cliente.tickets.index', compact('tickets'));
    }

    public function home()
    {
        return view('cliente.home'); // Esta es la vista que me acabas de mostrar
    }

    public function create()
    {
        $categorias = Categoria::all();
        $prioridades = Prioridad::all();
        $tiposEquipo = TipoEquipo::all();
        
        return view('cliente.tickets.create', compact('categorias', 'prioridades', 'tiposEquipo'));
    }

    public function store(Request $request)
    {
        // 1. Validar los datos
        $request->validate([
            'asunto' => 'required|string|max:200',
            'descripcion_problema' => 'required|string',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_tipo_equipo' => 'required|exists:tipos_equipo,id_tipo_equipo',
            'adjuntos.*' => 'nullable|file|max:10240', // Máx 10MB por archivo
        ]);

        // 2. Crear el Ticket
        $ticket = Ticket::create([
            'id_usuario' => auth()->id(),
            'asunto' => $request->asunto,
            'id_categoria' => $request->id_categoria,
            'id_tipo_equipo' => $request->id_tipo_equipo,
            'descripcion_problema' => $request->descripcion_problema,
            'id_prioridad' => 1, // Por defecto 'Baja' o según tu lógica
            'estatus' => 1, // 1 = Abierto
        ]);

        // 3. Procesar Adjuntos si existen
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $archivo) {
                // Guardar en la carpeta storage/app/public/tickets/ID
                $ruta = $archivo->store('tickets/' . $ticket->id_ticket, 'public');

                TicketAdjunto::create([
                    'id_ticket' => $ticket->id_ticket,
                    'ruta_archivo' => $ruta,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'tipo_mimo' => $archivo->getMimeType(),
                    'tamano' => $archivo->getSize(),
                ]);
            }
        }

        return redirect()->route('cliente.tickets.index')->with('success', 'Ticket creado correctamente.');
    }
}