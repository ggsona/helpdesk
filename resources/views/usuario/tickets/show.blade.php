<x-usuario-layout>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('usuario.tickets.index') }}" class="btn btn-link text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <h2 class="fw-bold mb-0 text-body">Ticket #{{ $ticket->id_ticket }}</h2>
                    <small class="">Creado el {{ $ticket->created_at->format('d/m/Y h:i A') }}</small>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->estatus == 0)
                        <span class="badge bg-secondary-subtle border border-secondary-subtle fs-6 px-3 shadow-sm">Borrador</span>
                        <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-send-check me-1"></i> Enviar a Soporte
                            </button>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 shadow-sm">Enviado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Información del Ticket --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3 text-body">Asunto: {{ $ticket->asunto }}</h5>
                        </div>
                        <p class=" border-start border-4 border-primary ps-3 py-2 bg-body rounded-end" style="white-space: pre-line;">{{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}</p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-paperclip me-2 text-primary"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card h-100 border border-light-subtle bg-body shadow-xs hover-shadow">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 140px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                <p class="small mb-0 text-uppercase text-secondary fw-bold">{{ $ext }}</p>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-transparent border-0 text-center py-2">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 border-0">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="mb-0 small"><i class="bi bi-info-circle me-1"></i> No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Chat / Comunicación --}}
                <div class="card border-0 shadow-sm bg-body-tertiary">
                    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-chat-dots me-2 text-primary"></i>Historial de Mensajes</h6>
                    </div>
                    
                    <div class="card-body bg-body-secondary bg-opacity-25 chat-container" style="max-height: 500px; overflow-y: auto; padding: 1.5rem;">
                        @php
                            $mensajesPublicos = $ticket->comentarios->where('es_interno', false);
                        @endphp

                        @if($mensajesPublicos->count() > 0)
                            @foreach($mensajesPublicos as $comentario)
                                <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div class="p-3 rounded-3 shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white' : 'bg-body border border-light-subtle' }}" style="max-width: 80%;">
                                        <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                                            <small class="fw-bold text-uppercase {{ $comentario->id_usuario == auth()->id() ? 'text-white-50' : 'text-primary' }}" style="font-size: 0.7rem;">
                                                {{ $comentario->usuario->name }} 
                                                @if($comentario->id_usuario != auth()->id()) 
                                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.6rem;">SOPORTE</span> 
                                                @endif
                                            </small>
                                            <small class="{{ $comentario->id_usuario == auth()->id() ? 'text-white-50' : 'text-secondary' }}" style="font-size: 0.65rem;">
                                                {{ $comentario->created_at->format('H:i | d/m/y') }}
                                            </small>
                                        </div>
                                        <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4; white-space: pre-wrap;">{{ $comentario->mensaje }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-text display-4 opacity-25"></i>
                                <p class="mt-3">Aún no hay mensajes públicos. Espera a que un técnico responda.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Caja de Texto --}}
                    @if($ticket->estatus != 0)
                        <div class="card-footer bg-transparent border-top border-light-subtle p-3">
                            <form action="{{ route('usuario.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <textarea name="mensaje" class="form-control bg-body border border-light-subtle shadow-none text-body" placeholder="Escribe tu mensaje aquí..." rows="2" required style="resize: none;"></textarea>
                                    <button class="btn btn-primary px-4" type="submit">
                                        <i class="bi bi-send-fill fs-5"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-footer bg-body-secondary bg-opacity-50 text-center py-3">
                            <span class="small"><i class="bi bi-lock-fill me-1"></i> El chat se habilitará cuando envíes el ticket a soporte.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar derecho --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-body border-bottom border-light-subtle pb-2">Información de Seguimiento</h6>
                        
                        {{-- Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="small d-block mb-3">Técnico Responsable: </label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center p-3 bg-body border border-light-subtle rounded-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ strtoupper(substr($ticket->tecnico->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-body" style="font-size: 0.95rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-primary fw-medium">Agente de Soporte</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning bg-warning-subtle text-warning-emphasis border-0 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-clock-history fs-5 me-2"></i>
                                    Pendiente por asignar un técnico.
                                </div>
                            @endif
                        </div>

                        <hr class="text-secondary opacity-25">

                        <div class="mb-3">
                            <label class="small d-block">Categoría</label>
                            <span class="fw-bold text-body"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small d-block">Equipo afectado</label>
                            <span class="fw-bold text-body"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>

                        <div class="mb-0">
                            <label class="small d-block mb-1">Prioridad del Caso: </label>
                            @if($ticket->prioridad)
                                <span class="badge py-2 px-3 {{ $ticket->prioridad->nombre_prioridad == 'Alta' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} border border-light-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="small italic">Evaluando prioridad...</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card de Ayuda --}}
                <div class="card bg-primary bg-gradient text-white border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle display-6 mb-3 opacity-50"></i>
                        <h6>¿Necesitas ayuda inmediata?</h6>
                        <p class="small opacity-75">Si el problema es crítico, puedes comunicarte con la extensión de soporte técnico en tu oficina.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-usuario-layout>