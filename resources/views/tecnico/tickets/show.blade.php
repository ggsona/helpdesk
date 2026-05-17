@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado Premium con Estética del Gestor --}}
    <div class="mb-4">
        <a href="{{ route('tecnico.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary mb-2">
            <i class="bi bi-arrow-left"></i> Volver al panel global
        </a>

        <div class="p-4 card-premium border-start border-5 d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h2 class="fw-bold mb-1 theme-text">Ticket #{{ $ticket->id_ticket }}</h2>
                <p class="text-secondary small mb-0">
                    <i class="bi bi-calendar3 me-1"></i> Asignado el {{ $ticket->asignacion ? $ticket->asignacion->created_at->format('d/m/Y h:i A') : $ticket->created_at->format('d/m/Y h:i A') }}
                </p>
            </div>

            <div class="d-flex gap-3 align-items-center">
                {{-- Badge de Estado Dinámico --}}
                <span class="badge bg-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-subtle text-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-emphasis border border-{{ $ticket->estatus == 3 ? 'success' : 'primary' }}-subtle px-3 py-2 rounded-pill">
                    <i class="bi bi-circle-fill me-1 small"></i> {{ $ticket->estado_texto }}
                </span>

                {{-- Acciones de Resolución --}}
                @if($ticket->estatus == 2)
                    <a href="{{ route('tecnico.tickets.resolver', $ticket->id_ticket) }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Resolver Ahora
                    </a>
                @elseif($ticket->estatus == 3)
                    <a href="{{ route('tecnico.tickets.editar-solucion', $ticket->id_ticket) }}" class="btn btn-warning text-white rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Editar Solución
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna Principal: Detalle, Adjuntos y Chat --}}
        <div class="col-lg-8">
            
            {{-- Detalle del Problema --}}
            <div class="card-premium mb-4 border-start border-5 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">{{ $ticket->asunto }}</h5>
                    <div class="p-3 rounded-3" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                        <p class="mb-0 theme-text">
                            {{ $ticket->descripcion_problema ?? 'Sin descripción técnica detallada.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Galería de Adjuntos (Integrada del Gestor) --}}
            <div class="card card-premium border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0 theme-text"><i class="bi bi-images me-2 text-primary"></i>Evidencias del Reporte</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($ticket->adjuntos as $archivo)
                            <div class="col-md-4">
                                <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-xs hover-zoom overflow-hidden">
                                    @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="Evidencia">
                                        </a>
                                    @else
                                        <div class="card-body text-center py-3">
                                            <i class="bi bi-file-earmark-zip fs-2 text-secondary"></i>
                                            <p class="small mb-0 text-uppercase fw-bold theme-text">{{ $ext }}</p>
                                        </div>
                                    @endif
                                    <div class="card-footer bg-transparent border-0 text-center pb-2">
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3">
                                <p class="text-secondary small mb-0 italic">No se adjuntaron archivos.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Chat de Comunicación (Mantiene lógica de Notas Privadas) --}}
            <div class="card card-premium border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-primary bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 theme-text"><i class="bi bi-chat-left-dots me-2"></i>Centro de Mensajes</h6>
                    <span class="badge bg-primary rounded-pill">Chat con Usuario</span>
                </div>
                
                <div class="card-body overflow-auto p-3" style="height: 450px; background: rgba(0,0,0,0.02);">
                    @forelse($ticket->comentarios as $comentario)
                        <div class="d-flex mb-4 {{ $comentario->id_usuario == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            @php
                                $esMio = $comentario->id_usuario == auth()->id();
                                $esInterno = $comentario->es_interno;
                            @endphp
                            <div class="p-3 rounded-4 shadow-sm {{ $esInterno ? 'bg-warning bg-opacity-10 text-warning border border-warning' : ($esMio ? 'bg-primary text-white' : 'bg-light border text-dark') }}" 
                                 style="max-width: 85%;">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-3">
                                    <small class="fw-bold text-uppercase" style="font-size: 0.6rem;">
                                        @if($esInterno) <i class="bi bi-eye-slash-fill me-1"></i> NOTA INTERNA @endif
                                        {{ $esMio ? 'Tú (Staff)' : $comentario->usuario->name }}
                                    </small>
                                    <small class="opacity-75" style="font-size: 0.6rem;">{{ $comentario->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="small mb-0">{{ $comentario->mensaje }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-chat-dots display-1 theme-text"></i>
                            <p class="theme-text mt-2">Sin actividad reciente.</p>
                        </div>
                    @endforelse
                </div>

                <div class="card-footer bg-transparent border-top p-3">
                    <form action="{{ route('tecnico.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="input-group shadow-sm mb-2">
                            <textarea name="mensaje" id="chat-textarea" class="form-control border-0 bg-light shadow-none" 
                                      placeholder="Escribir respuesta..." required style="resize: none;" rows="2"></textarea>
                            <button class="btn btn-primary px-3" type="submit"><i class="bi bi-send-fill"></i></button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" value="1" onchange="toggleNoteStyle(this)">
                                <label class="form-check-label small theme-muted" for="es_interno">Nota privada (solo staff)</label>
                            </div>
                            <small id="status-label" class="theme-muted italic" style="font-size: 0.75rem;">Visible para el cliente</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar: Información de Control --}}
        <div class="col-lg-4">
            {{-- Card del Usuario Solicitante --}}
            <div class="card-premium mb-4 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle shadow" style="width: 70px; height: 70px; font-size: 1.5rem; font-weight: bold;">
                        {{ strtoupper(substr($ticket->usuario->name, 0, 2)) }}
                    </div>
                    <h6 class="fw-bold mb-1 theme-text">{{ $ticket->usuario->name }}</h6>
                    <p class="text-secondary small mb-2">{{ $ticket->usuario->email }}</p>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 rounded-pill">
                        {{ $ticket->usuario->persona->oficina->nombre_oficina ?? 'S/O' }}
                    </span>
                </div>
            </div>

            {{-- Ficha Técnica Lateral --}}
            <div class="card card-premium shadow-sm mb-4 border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-uppercase small theme-text">Ficha Técnica</h6>
                    
                    <div class="mb-4">
                        <label class="text-secondary small d-block mb-1">Categoría</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-tag-fill me-2 text-primary"></i>
                            <span class="fw-bold theme-text">{{ $ticket->categoria->nombre_categoria ?? 'S/C' }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-secondary small d-block mb-1">Equipo Reportado</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-pc-display me-2 text-primary"></i>
                            <span class="fw-bold theme-text">{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No definido' }}</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="text-secondary small d-block mb-1">Nivel de Prioridad</label>
                        @php
                            $prioNom = strtolower($ticket->prioridad->nombre_prioridad ?? '');
                            $prioColor = str_contains($prioNom, 'crít') ? 'danger' : (str_contains($prioNom, 'alt') ? 'warning' : 'info');
                        @endphp
                        <div class="p-2 rounded-3 bg-{{ $prioColor }} bg-opacity-10 border border-{{ $prioColor }} border-opacity-25">
                            <span class="fw-bold text-{{ $prioColor }}">
                                <i class="bi bi-lightning-charge-fill me-1"></i> {{ strtoupper($ticket->prioridad->nombre_prioridad ?? 'Pendiente') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Información de la Solución (si existe) --}}
            @if($ticket->solucion)
            <div class="alert alert-success border-0 shadow-sm rounded-4">
                <h6 class="fw-bold"><i class="bi bi-journal-check me-2"></i>Solución Registrada</h6>
                <p class="small mb-0">{{ Str::limit($ticket->solucion->resumen_usuario, 100) }}</p>
            </div>
            @endif
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