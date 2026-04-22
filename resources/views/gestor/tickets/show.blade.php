@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado Premium --}}
    <div class="mb-4">
        <a href="{{ route('gestor.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2">
            <i class="bi bi-arrow-left"></i> Volver al panel global
        </a>
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fw-bold mb-1 text-white">Ticket #{{ $ticket->id_ticket }}</h2>
                <p class="text-muted small mb-0">
                    <i class="bi bi-calendar3 me-1"></i> Recibido el {{ $ticket->created_at->format('d/m/Y h:i A') }}
                </p>
            </div>
            <div class="card-body">
                @if($ticket->estatus == 1) {{-- Si está pendiente --}}
                    <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                        Asignar Técnico
                    </button>
                @elseif($ticket->estatus == 2) {{-- Si ya está asignado --}}
                    <button class="btn btn-warning w-100 mb-2 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                        Reasignar Técnico
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna Principal: Detalle y Chat --}}
        <div class="col-lg-8">
            {{-- Detalle del Problema --}}
            <div class="card bg-dark border-0 shadow-sm mb-4 border-start border-primary border-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">{{ $ticket->asunto }}</h5>
                    <div class="bg-black bg-opacity-25 p-3 rounded-3 border border-secondary">
                        <p class="mb-0 text-white-50" style="white-space: pre-line;">
                            {{ $ticket->descripcion_problema ?? 'Sin descripción técnica detallada.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Galería de Adjuntos (Premium) --}}
            <div class="card bg-dark border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-secondary py-3">
                    <h6 class="fw-bold mb-0 text-white"><i class="bi bi-images me-2 text-primary"></i>Evidencias y Archivos</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($ticket->adjuntos as $archivo)
                            <div class="col-md-4">
                                <div class="card h-100 bg-black bg-opacity-25 border-secondary shadow-xs hover-zoom">
                                    @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="Evidencia">
                                        </a>
                                    @else
                                        <div class="card-body text-center py-3">
                                            <i class="bi bi-file-earmark-zip fs-2 text-secondary"></i>
                                            <p class="small mb-0 text-uppercase fw-bold text-white-50">{{ $ext }}</p>
                                        </div>
                                    @endif
                                    <div class="card-footer bg-transparent border-0 text-center pb-2">
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-dark w-100 text-muted">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3">
                                <p class="text-muted small mb-0 italic">No hay archivos adjuntos en este ticket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Chat de Comunicación con el Usuario --}}
            <div class="card bg-dark border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-secondary bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-white"><i class="bi bi-chat-left-text me-2"></i>Centro de Mensajes</h6>
                    <span class="badge bg-primary">Canal Directo</span>
                </div>
                <div class="card-body bg-black bg-opacity-25 chat-container" style="height: 450px; overflow-y: auto;">
                    @forelse($ticket->comentarios as $comentario)
                        <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            {{-- Lógica de color para mensajes internos/privados --}}
                            <div class="p-3 shadow-sm {{ $comentario->es_interno ? 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' : ($comentario->id_usuario == auth()->id() ? 'bg-primary text-white rounded-chat-self' : 'bg-dark border border-secondary rounded-chat-other text-white-50') }}" style="max-width: 85%;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-uppercase" style="font-size: 0.65rem;">
                                        @if($comentario->es_interno) <i class="bi bi-eye-slash-fill me-1"></i> INTERNO: @endif
                                        {{ $comentario->id_usuario == auth()->id() ? 'Tú (Gestor)' : $comentario->usuario->name }}
                                    </small>
                                    <small class="ms-3 opacity-50" style="font-size: 0.6rem;">
                                        {{ $comentario->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="mb-0 small">{{ $comentario->mensaje }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 opacity-25">
                            <i class="bi bi-chat-square-dots display-4 text-white"></i>
                            <p class="mt-2 text-white">Inicia una conversación con el usuario.</p>
                        </div>
                    @endforelse
                </div>
                <div class="card-footer bg-transparent border-secondary p-3">
                    <form action="{{ route('gestor.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="input-group shadow-sm">
                            <textarea name="mensaje" class="form-control border-secondary bg-dark text-white" placeholder="Responder al usuario o añadir nota..." rows="2" style="resize: none;" required></textarea>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        {{-- Checkbox de Mensaje Interno --}}
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" value="1">
                            <label class="form-check-label small text-muted" for="es_interno">Nota privada (solo visible para personal técnico)</label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar: Información de Control --}}
        <div class="col-lg-4">
            {{-- Card de Usuario --}}
            <div class="card bg-dark border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-white">{{ $ticket->usuario->name }}</h6>
                    <p class="text-muted small mb-3">{{ $ticket->usuario->email }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-black bg-opacity-50 text-white-50 border border-secondary">{{ $ticket->usuario->persona->oficina->nombre_oficina ?? 'Sin Oficina' }}</span>
                    </div>
                </div>
            </div>

            {{-- Detalles Técnicos --}}
            <div class="card bg-dark border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 border-bottom border-secondary pb-2 text-uppercase small text-secondary">Ficha Técnica</h6>
                    
                    <div class="mb-3">
                        <label class="text-muted small d-block">Equipo Reportado</label>
                        <span class="fw-bold text-white"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No definido' }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small d-block">Prioridad Asignada</label>
                        <span class="fw-bold {{ ($ticket->prioridad->nombre_prioridad ?? '') == 'Alta' ? 'text-danger' : 'text-primary' }}">
                            <i class="bi bi-lightning-fill me-2"></i>{{ $ticket->prioridad->nombre_prioridad ?? 'Pendiente' }}
                        </span>
                    </div>

                    <div class="mb-0 text-center pt-3 border-top border-secondary mt-3">
                        <label class="text-muted small d-block mb-2">Técnico a Cargo</label>
                        @if($ticket->asignacion)
                            <div class="p-2 bg-success bg-opacity-10 rounded border border-success border-opacity-25">
                                <span class="fw-bold text-success">{{ $ticket->asignacion->tecnico->name }}</span>
                            </div>
                        @else
                            <div class="p-2 bg-warning bg-opacity-10 rounded border border-warning border-opacity-25">
                                <span class="text-warning fw-bold small">SIN TÉCNICO ASIGNADO</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .rounded-chat-self { border-radius: 15px 15px 2px 15px; }
    .rounded-chat-other { border-radius: 15px 15px 15px 2px; }
    .hover-zoom:hover { transform: scale(1.02); transition: 0.3s; }
    .chat-container::-webkit-scrollbar { width: 5px; }
    .chat-container::-webkit-scrollbar-thumb { background: #444; border-radius: 5px; }
</style>

{{-- Modal de Asignación/Reasignación (Tal cual estaba en tu original) --}}
<div class="modal fade" id="asignarModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-dark text-white">
            {{-- Header con degradado --}}
            <div class="modal-header border-0 bg-primary text-white p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bi bi-person-gear fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Gestionar Responsable</h5>
                        <small class="opacity-75">Ticket #{{ $ticket->id_ticket }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('gestor.tickets.asignar', $ticket->id_ticket) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 small mb-4 text-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        @if($ticket->asignacion)
                            Este ticket ya está asignado a <strong>{{ $ticket->asignacion->tecnico->name }}</strong>. Al seleccionar otro técnico, se notificará el cambio.
                        @else
                            Selecciona un técnico especializado para atender este requerimiento.
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Técnico Especialista</label>
                        <select name="id_usuario_tecnico" class="form-select border-secondary bg-black text-white p-3 shadow-none" required>
                            <option value="" selected disabled>Seleccionar técnico...</option>
                            @foreach($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}" {{ ($ticket->asignacion->id_usuario_tecnico ?? '') == $tecnico->id ? 'selected' : '' }}>
                                    {{ $tecnico->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Nota de Instrucción (Opcional)</label>
                        <textarea name="nota" class="form-control border-secondary bg-black text-white p-3 shadow-none" rows="3" placeholder="Escribe aquí alguna indicación técnica para el compañero..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 bg-black bg-opacity-25">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm rounded-pill">
                        <i class="bi bi-check2-circle me-1"></i> Confirmar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection