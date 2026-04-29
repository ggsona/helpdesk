@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold theme-text mb-0">Gestión de Ticket #{{ $ticket->id_ticket }}</h2>
            <a href="{{ route('tecnico.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Volver al panel global
            </a>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-subtle text-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-emphasis border border-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-subtle px-3 py-2 rounded-pill">
                <i class="bi bi-info-circle me-1"></i> {{ $ticket->estado_texto }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna de Información del Solicitante --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100" style="background: var(--bs-body-bg); border: 1px solid var(--border-color) !important;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold theme-text mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Datos del Solicitante</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: var(--bg-main);">
                        <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; font-weight: bold;">
                            {{ strtoupper(substr($ticket->usuario->name, 0, 2)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold theme-text">{{ $ticket->usuario->name }}</h6>
                            <small class="theme-muted">{{ $ticket->usuario->email }}</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small theme-muted text-uppercase fw-bold">Equipo</label>
                            <p class="theme-text mb-0"><i class="bi bi-pc-display me-1"></i> {{ $ticket->tipoEquipo->nombre_tipo_equipo }}</p>
                        </div>
                        <div class="col-6">
                            <label class="small theme-muted text-uppercase fw-bold">Prioridad</label>
                            <div>
                                @php
                                    $prioNom = strtolower($ticket->prioridad->nombre_prioridad ?? '');
                                    $prioColor = str_contains($prioNom, 'crít') ? 'danger' : (str_contains($prioNom, 'alt') ? 'warning' : 'info');
                                @endphp
                                <span class="badge bg-{{ $prioColor }}-subtle text-{{ $prioColor }}-emphasis border border-{{ $prioColor }}-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad ?? 'No asignada' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="small theme-muted text-uppercase fw-bold">Descripción del Problema</label>
                            <div class="mt-2 p-3 rounded-3 theme-text" style="background: var(--bg-main); border-left: 4px solid var(--bs-primary);">
                                {{ $ticket->descripcion_problema }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna del Chat de Comunicación --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden d-flex flex-column h-100" style="background: var(--bs-body-bg); border: 1px solid var(--border-color) !important; min-height: 550px;">
                <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                    <h6 class="fw-bold theme-text mb-0"><i class="bi bi-chat-left-dots me-2 text-primary"></i>Centro de Mensajes</h6>
                    <span class="badge bg-primary-subtle text-primary-emphasis small">Canal Directo</span>
                </div>
                
                <div class="card-body overflow-auto p-3" style="background: rgba(0,0,0,0.01); height: 400px;">
                    @forelse($ticket->comentarios as $comentario)
                        <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            @php
                                $esMio = $comentario->id_usuario == auth()->id();
                                $esInterno = $comentario->es_interno;
                                
                                if($esInterno) {
                                    $bubbleBg = 'rgba(255, 193, 7, 0.15)'; 
                                    $bubbleBorder = '#ffc107';
                                    $textColor = 'var(--text-main)';
                                } elseif($esMio) {
                                    $bubbleBg = 'var(--bs-primary)';
                                    $bubbleBorder = 'var(--bs-primary)';
                                    $textColor = '#ffffff';
                                } else {
                                    $bubbleBg = 'var(--bg-main)';
                                    $bubbleBorder = 'var(--border-color)';
                                    $textColor = 'var(--text-main)';
                                }
                            @endphp
                            <div class="p-3 rounded-4 shadow-sm" 
                                 style="max-width: 85%; background: {{ $bubbleBg }}; border: 1px solid {{ $bubbleBorder }}; color: {{ $textColor }};">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-3">
                                    <small class="fw-bold text-uppercase" style="font-size: 0.6rem; opacity: 0.8;">
                                        @if($esInterno) <i class="bi bi-eye-slash-fill me-1"></i> PRIVADO @endif
                                        {{ $esMio ? 'Tú (Staff)' : $comentario->usuario->name }}
                                    </small>
                                    <small class="opacity-50" style="font-size: 0.6rem;">
                                        {{ $comentario->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="small mb-0">{{ $comentario->mensaje }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-chat-dots display-1 theme-text"></i>
                            <p class="theme-text mt-2">No hay mensajes en este ticket.</p>
                        </div>
                    @endforelse
                </div>

                <div class="card-footer bg-transparent border-top p-3" style="border-color: var(--border-color) !important;">
                    <form action="{{ route('tecnico.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="input-group shadow-sm mb-2">
                            <textarea name="mensaje" id="chat-textarea" class="form-control border-0 bg-light shadow-none" 
                                      placeholder="Escribe tu respuesta..." required 
                                      style="background: var(--bg-main) !important; color: var(--text-main); resize: none;" rows="2"></textarea>
                            <button class="btn btn-primary px-3" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center px-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" value="1" onchange="toggleNoteStyle(this)">
                                <label class="form-check-label small theme-muted" for="es_interno">
                                    <i class="bi bi-eye-slash-fill me-1"></i> Nota privada
                                </label>
                            </div>
                            <small id="status-label" class="theme-muted italic" style="font-size: 0.75rem;">Visible para el cliente</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleNoteStyle(checkbox) {
    const textarea = document.getElementById('chat-textarea');
    const label = document.getElementById('status-label');
    if (checkbox.checked) {
        textarea.style.borderLeft = "4px solid #ffc107";
        label.innerText = "Modo: Nota Privada (Oculta)";
        label.classList.replace('theme-muted', 'text-warning');
    } else {
        textarea.style.borderLeft = "0";
        label.innerText = "Visible para el cliente";
        label.classList.replace('text-warning', 'theme-muted');
    }
}
</script>
@endsection