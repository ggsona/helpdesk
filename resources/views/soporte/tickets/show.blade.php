@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .ticket-chat-shell {
        height: 520px;
        overflow-y: auto;
        background: color-mix(in srgb, var(--bs-body-bg) 78%, var(--bs-tertiary-bg) 22%);
        border-top: 1px solid var(--bs-border-color);
        border-bottom: 1px solid var(--bs-border-color);
    }
    [data-bs-theme="dark"] .ticket-chat-shell {
        background: #15181b;
    }
    .chat-bubble-wrap {
        display: flex;
        gap: 0.65rem;
        align-items: flex-end;
        max-width: 88%;
    }
    .chat-bubble-wrap.mine {
        margin-left: auto;
        flex-direction: row-reverse;
    }
    .chat-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        flex-shrink: 0;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
    }
    .chat-bubble {
        border-radius: 16px;
        padding: 0.75rem 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid transparent;
    }
    .chat-bubble.public-mine {
        background: #0d6efd;
        color: #fff;
    }
    .chat-bubble.public-other {
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        border-color: var(--bs-border-color);
    }
    .chat-bubble.internal {
        background: rgba(255, 193, 7, 0.16);
        color: #664d03;
        border-color: rgba(255, 193, 7, 0.45);
    }
    [data-bs-theme="dark"] .chat-bubble.internal {
        color: #ffda6a;
        background: rgba(255, 193, 7, 0.14);
        border-color: rgba(255, 193, 7, 0.32);
    }
    .chat-meta {
        font-size: 0.67rem;
        letter-spacing: 0.02em;
    }
    .chat-meta.public-mine {
        color: rgba(255, 255, 255, 0.82);
    }
    .chat-meta.public-other {
        color: var(--bs-secondary-color);
    }
    .chat-text {
        white-space: pre-wrap;
        line-height: 1.45;
        font-size: 0.87rem;
    }
</style>
@endpush
<div class="container-fluid">
    {{-- Encabezado Premium con Botón de Regreso Dinámico --}}
    <div class="mb-4">
        @can('asignar-tickets')
            <a href="{{ route('soporte.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2 hover-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver a la mesa de despacho
            </a>
        @else
            <a href="{{ route('soporte.tickets.tecnico.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2 hover-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver a mi bandeja operativa
            </a>
        @endcan

        <div class="p-4 card-premium d-flex flex-wrap justify-content-between align-items-center shadow-sm">
            <div class="mb-3 mb-md-0">
                <h2 class="fw-bold mb-1 theme-text">Ticket #{{ $ticket->id_ticket }}</h2>
                <p class="text-secondary small mb-0">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> Reportado el {{ $ticket->created_at->format('d/m/Y h:i A') }}
                </p>
            </div>

            {{-- Acciones dinámicas del Encabezado según permisos y estados --}}
            <div>
                @can('asignar-tickets')
                    @if($ticket->estatus == 1) {{-- Por asignar --}}
                        <button class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                            <i class="bi bi-person-plus-fill"></i> Asignar Especialista
                        </button>
                    @elseif($ticket->estatus == 2) {{-- En gestión --}}
                        <button class="btn btn-warning text-dark rounded-3 px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                            <i class="bi bi-arrow-repeat"></i> Reasignar Caso
                        </button>
                    @endif
                @else
                    @can('resolver-tickets')
                        @if($ticket->estatus == 2 && $ticket->asignacion->id_usuario_tecnico == Auth::id())
                            <a href="{{ route('soporte.tickets.tecnico.resolver', $ticket->id_ticket) }}" class="btn btn-success rounded-3 px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle"></i> Resolver Incidente
                            </a>
                        @elseif($ticket->estatus == 3 && $ticket->asignacion->id_usuario_tecnico == Auth::id())
                            <a href="{{ route('soporte.tickets.tecnico.editar-solucion', $ticket->id_ticket) }}" class="btn btn-outline-warning border-warning-subtle text-warning rounded-3 px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square"></i> Editar Solución
                            </a>
                        @endif
                    @endcan
                @endcan
            </div>
        </div>
    </div>

    {{-- Cuerpo de la Ficha del Ticket --}}
    <div class="row g-4">
        {{-- Columna Principal: Detalle y Chat --}}
        <div class="col-lg-8">
            {{-- Detalle del Problema --}}
            <div class="card-premium mb-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">{{ $ticket->asunto }}</h5>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-3 theme-bg-dark">
                        <p class="mb-0 theme-text" style="white-space: pre-line; line-height: 1.6;">{{ $ticket->descripcion_problema ?? 'Sin descripción técnica detallada.' }}</p>
                    </div>
                </div>
            </div>

            {{-- Galería de Adjuntos Multimedia --}}
            <div class="card card-premium border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h6 class="fw-bold mb-0 theme-text"><i class="bi bi-images me-2 text-primary"></i>Archivos Adjuntos y Evidencias</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($ticket->adjuntos as $archivo)
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="card h-100 bg-secondary bg-opacity-10 border-secondary border-opacity-25 shadow-xs hover-zoom theme-bg-dark">
                                    @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="Evidencia">
                                        </a>
                                    @else
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-file-earmark-zip fs-1 text-secondary"></i>
                                            <p class="small mb-0 text-uppercase fw-bold mt-2 theme-text">{{ $ext }}</p>
                                        </div>
                                    @endif
                                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 rounded-3 d-inline-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4">
                                <i class="bi bi-cloud-slash display-6 text-muted mb-2 d-block"></i>
                                <p class="text-secondary small mb-0 italic">No hay archivos adjuntos provistos en este ticket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Chat de Comunicación con el Usuario --}}
            <div class="card card-premium border-0 shadow-sm overflow-hidden mb-5">
                <div class="card-header bg-secondary bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 theme-text"><i class="bi bi-chat-dots-fill me-2 text-primary"></i>Conversación del Caso</h6>
                    <span class="badge bg-primary rounded-pill">Chat en tiempo real</span>
                </div>
                
                {{-- Contenedor del Chat --}}
                <div class="card-body ticket-chat-shell p-4" id="ticket-chat-shell">
                    @forelse($ticket->comentarios as $comentario)
                        @php
                            $esMio = $comentario->id_usuario == auth()->id();
                            $esGestor = $comentario->usuario->hasRole('gestor');
                            $esTecnico = $comentario->usuario->hasRole('tecnico');
                            $esAdmin = $comentario->usuario->hasRole('admin');
                            
                            $remitente = 'Cliente';
                            if ($esMio) {
                                $remitente = 'Tú (' . ($esAdmin ? 'Admin' : ($esGestor ? 'Coordinador' : 'Especialista')) . ')';
                            } elseif ($esAdmin) {
                                $remitente = 'Sistemas (' . $comentario->usuario->name . ')';
                            } elseif ($esGestor) {
                                $remitente = 'Coordinador (' . $comentario->usuario->name . ')';
                            } elseif ($esTecnico) {
                                $remitente = 'Especialista (' . $comentario->usuario->name . ')';
                            } else {
                                $remitente = 'Cliente (' . $comentario->usuario->name . ')';
                            }
                            $avatar = strtoupper(substr($comentario->usuario->name ?? 'S', 0, 1));
                            $tipoBurbuja = $comentario->es_interno
                                ? 'internal'
                                : ($esMio ? 'public-mine' : 'public-other');
                        @endphp
                        <div class="mb-4">
                            <div class="chat-bubble-wrap {{ $esMio ? 'mine' : '' }}">
                                <div class="chat-avatar">{{ $avatar }}</div>
                                <div class="chat-bubble {{ $tipoBurbuja }}">
                                    <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                        <small class="fw-semibold chat-meta {{ $esMio && !$comentario->es_interno ? 'public-mine' : 'public-other' }}">
                                            @if($comentario->es_interno)
                                                <i class="bi bi-eye-slash-fill me-1"></i> Nota interna ·
                                            @endif
                                            {{ $remitente }}
                                        </small>
                                        <small class="chat-meta {{ $esMio && !$comentario->es_interno ? 'public-mine' : 'public-other' }}">
                                            {{ $comentario->created_at->format('H:i') }} · {{ $comentario->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="mb-0 chat-text">{{ $comentario->mensaje }}</p>
                                    @if($comentario->es_interno)
                                        <small class="d-block mt-2 chat-meta public-other">
                                            Visible solo para staff de soporte.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-chat-square-dots display-3 mb-2 d-block theme-text"></i>
                            <p class="mt-2 text-muted mb-0">No hay mensajes registrados. Comienza una conversación.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pie del Chat: Caja de envío con validación de Nota Interna --}}
                <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-3">
                    @php
                        // Decidir a qué ruta enviar el comentario según los permisos
                        $rutaComentar = auth()->user()->hasRole('gestor') || auth()->user()->hasRole('admin')
                            ? route('soporte.tickets.comentar', $ticket->id_ticket)
                            : route('soporte.tickets.tecnico.comentar', $ticket->id_ticket);
                    @endphp
                    <form action="{{ $rutaComentar }}" method="POST">
                        @csrf
                        <div class="input-group shadow-sm">
                            <textarea name="mensaje" id="chat-textarea" class="form-control form-control-premium" placeholder="Escribe un mensaje de respuesta o nota..." rows="2" style="resize: none;" required></textarea>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            @can('comentar-interno')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" value="1" onchange="toggleNoteStyle(this)">
                                    <label class="form-check-label small text-secondary fw-semibold" for="es_interno">
                                        <i class="bi bi-eye-slash-fill me-1"></i> Enviar como Nota Interna (Privada para el Staff)
                                    </label>
                                </div>
                            @endcan
                            <small id="status-label" class="text-muted small italic">Visible para el cliente</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Lateral: Ficha Informativa del Incidente --}}
        <div class="col-lg-4">
            {{-- Tarjeta del Cliente --}}
            <div class="card-premium mb-4 shadow-sm text-center p-3 p-lg-4" style="min-width: 0;">
                <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; flex-shrink: 0;">
                    <i class="bi bi-person-circle fs-2 text-secondary opacity-75"></i>
                </div>
                <h6 class="fw-bold mb-1 theme-text text-truncate" title="{{ $ticket->usuario->name }}">{{ $ticket->usuario->name }}</h6>
                <p class="text-secondary small mb-3 text-truncate" title="{{ $ticket->usuario->email }}">{{ $ticket->usuario->email }}</p>
                <div class="d-flex align-items-start gap-2 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 p-2 text-start" style="font-size: 0.8rem; min-width: 0;">
                    <i class="bi bi-building opacity-60 flex-shrink-0 mt-1"></i>
                    <div style="min-width: 0; flex: 1;">
                        @if($ticket->usuario->persona->unidadAdministrativa)
                            <span class="fw-semibold text-secondary d-block" style="word-break: break-word;">{{ $ticket->usuario->persona->unidadAdministrativa->nombre }}</span>
                            @if($ticket->usuario->persona->unidadAdministrativa->trashed())
                                <span class="text-danger d-block mt-1" style="font-size: 0.7rem; word-break: break-word;">
                                    <i class="bi bi-archive-fill me-1"></i>Archivada el {{ date('d/m/Y', strtotime($ticket->usuario->persona->unidadAdministrativa->deleted_at)) }}
                                </span>
                            @endif
                        @else
                            <span class="text-secondary">Sin Sede registrada</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ficha de Control Técnico --}}
            <div class="card-premium shadow-sm mb-4 p-3 p-lg-4" style="min-width: 0;">
                <h6 class="fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">Detalles Técnicos</h6>
                
                <div class="mb-3">
                    <label class="text-secondary small d-block mb-1">Categoría General</label>
                    <span class="fw-bold theme-text"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'S/C' }}</span>
                </div>

                <div class="mb-3">
                    <label class="text-secondary small d-block mb-1">Dispositivo Relacionado</label>
                    <span class="fw-bold theme-text"><i class="bi bi-laptop me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No definido' }}</span>
                </div>

                <div class="mb-3">
                    <label class="text-secondary small d-block mb-1">Estado de Urgencia</label>
                    @php
                        $prioridad = $ticket->prioridad->nombre_prioridad ?? 'N/A';
                        $textClass = match($prioridad) {
                            'Crítica', 'Critica' => 'text-danger',
                            'Alta' => 'text-warning',
                            'Media' => 'text-info',
                            'Baja' => 'text-success',
                            default => 'text-secondary'
                        };
                    @endphp
                    <span class="fw-bold {{ $textClass }}">
                        <i class="bi bi-lightning-fill me-2"></i>{{ $prioridad }}
                    </span>
                </div>

                <div class="mb-0 pt-4 border-top border-secondary border-opacity-25 mt-4" style="min-width: 0;">
                    <label class="text-secondary small d-block mb-2 fw-semibold">Especialista Asignado</label>
                    @if($ticket->asignacion)
                        <div class="p-2 bg-success bg-opacity-10 rounded border border-success border-opacity-25 shadow-sm">
                            <span class="fw-bold text-success small d-flex align-items-center gap-1" style="word-break: break-word;">
                                <i class="bi bi-person-check-fill flex-shrink-0"></i>
                                <span>{{ $ticket->asignacion->tecnico->name }}</span>
                            </span>
                        </div>
                    @else
                        <div class="p-2 bg-warning bg-opacity-10 rounded border border-warning border-opacity-25 shadow-sm">
                            <span class="text-warning fw-bold small d-flex align-items-center gap-1">
                                <i class="bi bi-person-fill-dash flex-shrink-0"></i>
                                <span>Sin técnico asignado</span>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales para Gestores --}}
@can('asignar-tickets')
    @include('soporte.tickets.modals.asignar')
    @include('soporte.tickets.modals.reasignar')
@endcan

{{-- Script Dinámico para Nota Interna --}}
<script>
function toggleNoteStyle(checkbox) {
    const textarea = document.getElementById('chat-textarea');
    const label = document.getElementById('status-label');
    if (checkbox.checked) {
        textarea.style.borderLeft = "5px solid #ffc107";
        textarea.style.backgroundColor = "rgba(255, 193, 7, 0.03)";
        label.innerText = "Modo: Nota Privada Oculta";
        label.classList.replace('text-muted', 'text-warning');
        label.classList.add('fw-bold');
    } else {
        textarea.style.borderLeft = "1px solid var(--bs-border-color)";
        textarea.style.backgroundColor = "transparent";
        label.innerText = "Visible para el cliente";
        label.classList.replace('text-warning', 'text-muted');
        label.classList.remove('fw-bold');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const chatShell = document.getElementById("ticket-chat-shell");
    if (chatShell) {
        chatShell.scrollTop = chatShell.scrollHeight;
    }
});
</script>
@endsection
