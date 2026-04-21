<x-usuario-layout>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('cliente.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <h2 class="fw-bold mb-0">Ticket #{{ $ticket->id_ticket }}</h2>
                    <small class="text-muted">Creado el {{ $ticket->created_at->format('d/m/Y h:i A') }}</small>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->estatus == 0)
                        <span class="badge bg-secondary fs-6 px-3 shadow-sm">Borrador</span>
                        <a href="{{ route('cliente.tickets.edit', $ticket->id_ticket) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('cliente.tickets.enviar', $ticket->id_ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-send-check"></i> Enviar a Soporte
                            </button>
                        </form>
                    @else
                        <span class="badge bg-primary fs-6 px-3 shadow-sm">Enviado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Información del Ticket --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3 text-dark">Asunto: {{ $ticket->asunto }}</h5>
                        </div>
                        <p class="text-muted border-start border-4 ps-3 py-2 bg-light rounded-end">
                            {{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}
                        </p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-paperclip me-2 text-primary"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card h-100 border shadow-xs hover-shadow">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 140px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                <p class="small mb-0 text-uppercase text-muted fw-bold">{{ $ext }}</p>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-light border-0 text-center py-2">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 border-0">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="text-muted mb-0 small"><i class="bi bi-info-circle me-1"></i> No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Chat / Comunicación --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-dots me-2 text-primary"></i>Historial de Mensajes</h6>
                    </div>
                    
                    <div class="card-body bg-light chat-container" style="max-height: 500px; overflow-y: auto; padding: 1.5rem;">
                        @forelse($ticket->comentarios as $comentario)
                            {{-- Solo mostramos comentarios que no sean internos --}}
                            @if(!$comentario->es_interno)
                                <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div class="p-3 rounded-3 shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 80%;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="fw-bold {{ $comentario->id_usuario == auth()->id() ? 'text-white-50' : 'text-primary' }} text-uppercase" style="font-size: 0.7rem;">
                                                {{ $comentario->usuario->name }} 
                                                @if($comentario->id_usuario != auth()->id()) <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.6rem;">SOPORTE</span> @endif
                                            </small>
                                            <small class="ms-3 {{ $comentario->id_usuario == auth()->id() ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.65rem;">
                                                {{ $comentario->created_at->format('H:i | d/m/y') }}
                                            </small>
                                        </div>
                                        <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4;">{{ $comentario->mensaje }}</p>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-text display-4 text-muted opacity-25"></i>
                                <p class="text-muted mt-3">Aún no hay mensajes. Espera a que un técnico responda.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Caja de Texto --}}
                    @if($ticket->estatus != 0)
                        <div class="card-footer bg-white border-top p-3">
                            <form action="{{ route('cliente.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <textarea name="mensaje" class="form-control border shadow-none" placeholder="Escribe tu mensaje aquí..." rows="2" required style="resize: none;"></textarea>
                                    <button class="btn btn-primary px-4" type="submit">
                                        <i class="bi bi-send-fill fs-5"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-footer bg-white text-center py-3">
                            <span class="text-muted small"><i class="bi bi-lock-fill me-1"></i> El chat se habilitará cuando envíes el ticket a soporte.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar derecho --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-dark border-bottom pb-2">Información de Seguimiento</h6>
                        
                        {{-- Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="text-muted small d-block mb-2">Técnico Responsable</label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center p-3 bg-primary-subtle rounded-3 border border-primary-subtle">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ strtoupper(substr($ticket->tecnico->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-dark" style="font-size: 0.95rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-primary fw-medium">Agente de Soporte</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-clock-history fs-5 me-2"></i>
                                    Pendiente por asignar un técnico.
                                </div>
                            @endif
                        </div>

                        <hr class="text-muted opacity-25">

                        <div class="mb-3">
                            <label class="text-muted small d-block">Categoría</label>
                            <span class="fw-bold text-dark"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted small d-block">Equipo afectado</label>
                            <span class="fw-bold text-dark"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>

                        <div class="mb-0">
                            <label class="text-muted small d-block mb-1">Prioridad del Caso</label>
                            @if($ticket->prioridad)
                                <span class="badge py-2 px-3 {{ $ticket->prioridad->nombre_prioridad == 'Alta' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} border">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="text-muted small italic">Evaluando prioridad...</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card de Ayuda --}}
                <div class="card bg-dark text-white border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle display-6 mb-3 text-primary"></i>
                        <h6>¿Necesitas ayuda inmediata?</h6>
                        <p class="small text-white-50">Si el problema es crítico, puedes comunicarte con la extensión de soporte técnico en tu oficina.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .chat-container::-webkit-scrollbar {
            width: 6px;
        }
        .chat-container::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 10px;
        }
    </style>
</x-usuario-layout>