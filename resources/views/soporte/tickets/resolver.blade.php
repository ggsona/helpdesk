@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('soporte.tickets.tecnico.index') }}" class="text-decoration-none small text-muted hover-primary">
            <i class="bi bi-arrow-left me-1"></i> Volver a mi bandeja operativa
        </a>
    </div>

    <form action="{{ route('soporte.tickets.tecnico.guardar-solucion', $ticket->id_ticket) }}" method="POST" id="formSolucion">
        @csrf
        <div class="row g-4">
            {{-- Panel Izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="fw-bold theme-text mb-0"><i class="bi bi-journal-plus text-success me-2"></i>Documentar Solución Técnica</h5>
                        <small class="text-muted">Este contenido alimentará la Base de Conocimientos técnica del sistema.</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label theme-text fw-bold">Título / Resumen para el Usuario</label>
                            <input type="text" name="resumen_usuario" class="form-control form-control-premium @error('resumen_usuario') is-invalid @enderror" 
                                   placeholder="Ej: Reemplazo de memoria RAM dañada y limpieza interna" required>
                            @error('resumen_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label theme-text fw-bold">Diagnóstico Inicial</label>
                                <textarea name="diagnostico" class="form-control form-control-premium" rows="2" placeholder="¿Qué se encontró al revisar el problema?"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label theme-text fw-bold">Causa Raíz</label>
                                <textarea name="causa_raiz" class="form-control form-control-premium" rows="2" placeholder="¿Por qué ocurrió el problema?"></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label theme-text fw-bold">Procedimiento Detallado (Paso a paso)</label>
                            {{-- Contenedor para Quill --}}
                            <div id="editor" style="height: 300px;" class="theme-text"></div>
                            {{-- Input oculto para enviar el HTML al servidor --}}
                            <input type="hidden" name="procedimiento_detallado" id="procedimiento_detallado">
                            @error('procedimiento_detallado') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label theme-text fw-bold">Acciones Preventivas (Opcional)</label>
                            <textarea name="acciones_preventivas" class="form-control form-control-premium" rows="2" placeholder="¿Qué hacer para que no vuelva a ocurrir?"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Metadatos --}}
            <div class="col-lg-4">
                <div class="card card-premium shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold theme-text mb-3">Detalles del Ticket</h6>
                        
                        <div class="p-3 rounded-3 mb-3 bg-body-secondary theme-bg-dark border border-secondary border-opacity-10">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Ticket Original</small>
                            <span class="theme-text fw-semibold d-block">#{{ $ticket->id_ticket }}</span>
                            <span class="theme-text text-muted small d-block">{{ Str::limit($ticket->asunto, 60) }}</span>
                        </div>
                        
                        <div class="p-3 rounded-3 mb-4 bg-body-secondary theme-bg-dark border border-secondary border-opacity-10">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Usuario Afectado</small>
                            <span class="theme-text fw-semibold d-block"><i class="bi bi-person me-1"></i>{{ $ticket->usuario->name }}</span>
                            @if($ticket->usuario->persona->unidadAdministrativa)
                                <span class="theme-text text-muted small d-block">
                                    {{ $ticket->usuario->persona->unidadAdministrativa->nombre }}
                                    @if($ticket->usuario->persona->unidadAdministrativa->trashed())
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 ms-1 fw-bold" style="font-size: 0.65rem;">(Archivada el {{ date('d/m/Y', strtotime($ticket->usuario->persona->unidadAdministrativa->deleted_at)) }})</span>
                                    @endif
                                </span>
                            @else
                                <span class="theme-text text-muted small d-block">Sin Sede</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-3 mb-4 theme-bg-dark border border-secondary border-opacity-10">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Métricas de Solución</small>
                            <div class="mb-2">
                                <label class="small text-secondary mb-1">Dificultad</label>
                                <select name="dificultad" class="form-select form-select-sm theme-text" style="background-color: var(--bg-main);">
                                    <option value="basica">Básica</option>
                                    <option value="intermedia" selected>Intermedia</option>
                                    <option value="avanzada">Avanzada</option>
                                </select>
                            </div>
                            <div>
                                <label class="small text-secondary mb-1">Tiempo de Resolución Estimado</label>
                                <input type="text" name="tiempo_resolucion" class="form-control form-control-sm theme-text" style="background-color: var(--bg-main);" placeholder="Ej: 30 mins, 2 horas">
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mb-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="publicar_en_kb" name="publicar_en_kb" value="1">
                                <label class="form-check-label fw-bold text-primary" for="publicar_en_kb">Publicar en Base de Conocimiento</label>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Si marcas esto, esta solución se convertirá en un artículo de la Wiki para que otros técnicos puedan consultarlo en el futuro.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm mb-2 fw-bold">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Publicar y Finalizar
                        </button>
                        <a href="{{ route('soporte.tickets.tecnico.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Scripts de Quill --}}
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Describe detalladamente el paso a paso del procedimiento técnico realizado...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Antes de enviar el form, pasamos el contenido de Quill al input oculto
    document.getElementById('formSolucion').onsubmit = function() {
        var contenido = document.querySelector('input[id=procedimiento_detallado]');
        contenido.value = quill.root.innerHTML;
    };
</script>

<style>
    .ql-toolbar { background: transparent !important; border-top-left-radius: 10px; border-top-right-radius: 10px; border-color: var(--bs-border-color) !important; }
    .ql-container { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; background: var(--bg-main); color: var(--text-main); font-size: 1rem; border-color: var(--bs-border-color) !important; }
    .ql-toolbar .ql-stroke { stroke: var(--text-main); }
    .ql-toolbar .ql-fill { fill: var(--text-main); }
    .ql-toolbar .ql-picker { color: var(--text-main); }
    /* Soporte Dark Mode para Quill Editor */
    [data-bs-theme="dark"] .ql-toolbar.ql-snow { border-color: #323248; background-color: #1e1e2d; }
    [data-bs-theme="dark"] .ql-container.ql-snow { border-color: #323248; background-color: #1e1e2d; color: #fff; }
    [data-bs-theme="dark"] .ql-snow .ql-stroke { stroke: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-fill { fill: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-picker { color: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-picker-options { background-color: #1e1e2d; border-color: #323248; }
</style>
@endsection
