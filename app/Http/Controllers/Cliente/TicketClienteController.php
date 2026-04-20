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
            'adjuntos.*' => 'nullable|file|max:10240',
        ]);

        // 2. Crear el Ticket como BORRADOR
        $ticket = Ticket::create([
            'id_usuario' => auth()->id(),
            'asunto' => $request->asunto,
            'id_categoria' => $request->id_categoria,
            'id_tipo_equipo' => $request->id_tipo_equipo,
            'descripcion_problema' => $request->descripcion_problema,
            'id_prioridad' => null, // Opcional en borrador
            'estatus' => 0, // <--- CAMBIO: 0 ahora significa "Borrador"
        ]);

        // 3. Procesar Adjuntos
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $archivo) {
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

        // Retornamos a la lista con un mensaje que aclare que es un borrador
        return redirect()->route('cliente.tickets.index')
            ->with('success', 'Ticket guardado como borrador. Puedes revisarlo antes de enviarlo.');
    }

    public function enviar(Ticket $ticket) 
    {
        // Verificamos que el ticket pertenezca al usuario para evitar que alguien envíe tickets ajenos
        if ($ticket->id_usuario !== auth()->id()) {
            abort(403);
        }

        $ticket->update(['estatus' => 1]); // Pasa de Borrador (0) a Abierto (1)

        return back()->with('success', '¡Ticket enviado con éxito al equipo de soporte!');
    }

        public function show($id)
    {
        // Cargamos el técnico y la categoría para el detalle
        $ticket = Ticket::with(['categoria', 'tecnico', 'prioridad', 'adjuntos'])
                        ->where('id_usuario', auth()->id())
                        ->findOrFail($id);
                        
        return view('cliente.tickets.show', compact('ticket'));
    }

    public function edit($id)
    {
        // Solo permitimos editar si es borrador (estatus 0)
        $ticket = Ticket::where('id_usuario', auth()->id())
                        ->where('estatus', 0)
                        ->findOrFail($id);
                        
        $categorias = Categoria::all();
        return view('cliente.tickets.edit', compact('ticket', 'categorias'));
    }

    public function destroy($id)
    {
        $ticket = Ticket::where('id_usuario', auth()->id())
                        ->where('estatus', 0)
                        ->findOrFail($id);
        
        // Opcional: Eliminar archivos físicos antes de borrar el registro
        $ticket->delete();

        return redirect()->route('cliente.tickets.index')
                        ->with('success', 'Ticket eliminado correctamente.');
    }

    public function update(Request $request, $id)
    {
        // 1. Validar que el ticket pertenezca al usuario y sea borrador
        $ticket = Ticket::where('id_usuario', auth()->id())
                        ->where('estatus', 0)
                        ->findOrFail($id);

        // 2. Validar los datos de entrada
        $request->validate([
            'asunto' => 'required|string|max:255',
            'descripcion_problema' => 'nullable|string',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'archivos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // 3. Actualizar datos básicos
        $ticket->update([
            'asunto' => $request->asunto,
            'descripcion_problema' => $request->descripcion_problema,
            'id_categoria' => $request->id_categoria,
        ]);

        // 4. Manejar nuevos archivos adjuntos (si existen)
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                $nombreArchivo = time() . '_' . $file->getClientOriginalName();
                $ruta = $file->storeAs('adjuntos_tickets', $nombreArchivo, 'public');

                // Guardar referencia en la tabla de adjuntos
                ArchivoTicket::create([
                    'id_ticket' => $ticket->id_ticket,
                    'ruta_archivo' => $ruta,
                    'nombre_original' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('cliente.tickets.index')
                        ->with('success', 'Borrador actualizado correctamente.');
    }
}