@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado Premium --}}
    <div class="mb-4">
        <a href="{{ route('gestor.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2">
            <i class="bi bi-arrow-left"></i> Volver al panel global
        </a>

        <div class="p-4 card-premium border-start border-primary border-5 d-flex justify-content-between align-items-end shadow-sm">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="fw-bold mb-1">Ticket #{{ $ticket->id_ticket }}</h2>
                    <p class="text-secondary small mb-0">
                        <i class="bi bi-calendar3 me-1"></i> Recibido el {{ $ticket->created_at->format('d/m/Y h:i A') }}
                    </p>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end">
                <div class="card-body p-0">
                    @if($ticket->estatus == 1) {{-- Si está pendiente --}}
                        <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                            Asignar Técnico
                        </button>
                    @elseif($ticket->estatus == 2) {{-- Si ya está asignado --}}
                        <button class="btn btn-warning w-100 mb-2 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                            Reasignar Técnico
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna Principal: Detalle y Chat --}}
        <div class="col-lg-8">
            {{-- Detalle del Problema --}}
            <div class="card-premium mb-4 border-start border-primary border-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">{{ $ticket->asunto }}</h5>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-3">
                        <p class="mb-0">
                            {{ $ticket->descripcion_problema ?? 'Sin descripción técnica detallada.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Galería de Adjuntos --}}
            <div class="card card-premium border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-images me-2 text-primary"></i>Evidencias y Archivos</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($ticket->adjuntos as $archivo)
                            <div class="col-md-4">
                                <div class="card h-100 bg-secondary bg-opacity-10 border-secondary border-opacity-25 shadow-xs hover-zoom">
                                    @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="Evidencia">
                                        </a>
                                    @else
                                        <div class="card-body text-center py-3">
                                            <i class="bi bi-file-earmark-zip fs-2 text-secondary"></i>
                                            <p class="small mb-0 text-uppercase fw-bold">{{ $ext }}</p>
                                        </div>
                                    @endif
                                    <div class="card-footer bg-transparent border-0 text-center pb-2">
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3">
                                <p class="text-secondary small mb-0 italic">No hay archivos adjuntos en este ticket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Chat de Comunicación con el Usuario --}}
            <div class="card card-premium border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-secondary bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-chat-left-text me-2"></i>Centro de Mensajes</h6>
                    <span class="badge bg-primary">Canal Directo</span>
                </div>
                <div class="card-body chat-container" style="height: 450px; overflow-y: auto; background-color: rgba(0,0,0, 0.02);">
                    @forelse($ticket->comentarios as $comentario)
                        <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="p-3 shadow-sm {{ $comentario->es_interno ? 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' : ($comentario->id_usuario == auth()->id() ? 'bg-primary text-white rounded-3' : 'bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3') }}" style="max-width: 85%;">
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
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-chat-square-dots display-4"></i>
                            <p class="mt-2">Inicia una conversación con el usuario.</p>
                        </div>
                    @endforelse
                </div>
                <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-3">
                    <form action="{{ route('gestor.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="input-group shadow-sm">
                            <textarea name="mensaje" id="chat-textarea" class="form-control form-control-premium" placeholder="Responder al usuario o añadir nota..." rows="2" style="resize: none;" required></textarea>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" value="1" onchange="toggleNoteStyle(this)">
                                <label class="form-check-label small text-secondary" for="es_interno">
                                    <i class="bi bi-eye-slash-fill me-1"></i> Nota privada (Solo staff)
                                </label>
                            </div>
                            <small id="status-label" class="text-muted small italic">Visible para el cliente</small>
                        </div>
                    </form>
                </div>

                <script>
                function toggleNoteStyle(checkbox) {
                    const textarea = document.getElementById('chat-textarea');
                    const label = document.getElementById('status-label');
                    if (checkbox.checked) {
                        textarea.style.borderLeft = "4px solid #ffc107"; // Color warning de nota privada
                        label.innerText = "Modo: Nota Privada Oculta";
                        label.classList.replace('text-muted', 'text-warning');
                    } else {
                        textarea.style.borderLeft = "1px solid var(--border-color)";
                        label.innerText = "Visible para el cliente";
                        label.classList.replace('text-warning', 'text-muted');
                    }
                }
                </script>
            </div>
        </div>

        {{-- Sidebar: Información de Control --}}
        <div class="col-lg-4">
            {{-- Card de Usuario --}}
            <div class="card-premium mb-4 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $ticket->usuario->name }}</h6>
                    <p class="text-secondary small mb-1">{{ $ticket->usuario->email }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25">{{ $ticket->usuario->persona->oficina->nombre_oficina ?? 'Sin Oficina' }}</span>
                    </div>
                </div>
            </div>

            {{-- Detalles Técnicos --}}
            <div class="card card-premium shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2 text-uppercase small text-secondary">Ficha Técnica</h6>
                    
                    <div class="mb-3">
                        <label class="text-secondary small d-block">Equipo Reportado</label>
                        <span class="fw-bold"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No definido' }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="text-secondary small d-block">Prioridad Asignada</label>
                        <span class="fw-bold {{ ($ticket->prioridad->nombre_prioridad ?? '') == 'Alta' ? 'text-danger' : 'text-primary' }}">
                            <i class="bi bi-lightning-fill me-2"></i>{{ $ticket->prioridad->nombre_prioridad ?? 'Pendiente' }}
                        </span>
                    </div>

                    <div class="mb-0 text-center pt-3 border-top border-secondary border-opacity-25 mt-3">
                        <label class="text-secondary small d-block mb-2">Técnico a Cargo</label>
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

@include('gestor.tickets.modals.asignar')
@include('gestor.tickets.modals.reasignar')

@endsection