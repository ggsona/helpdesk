@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('soporte.tickets.tecnico.index') }}" class="text-decoration-none small text-muted hover-primary">
            <i class="bi bi-arrow-left me-1"></i> Volver a mi bandeja operativa
        </a>
    </div>

    <form action="{{ route('soporte.tickets.tecnico.actualizar-solucion', $ticket->id_ticket) }}" method="POST" id="formSolucion">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            {{-- Panel Izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="fw-bold theme-text mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Editar Solución Técnica</h5>
                        <small class="text-muted">Modifica los detalles del caso para enriquecer la Base de Conocimientos técnica.</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label theme-text fw-bold">Título / Resumen para el Usuario</label>
                            <input type="text" name="resumen_usuario" class="form-control form-control-premium @error('resumen_usuario') is-invalid @enderror" 
                                   value="{{ old('resumen_usuario', $ticket->solucion->resumen_usuario) }}" required>
                            @error('resumen_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label theme-text fw-bold">Procedimiento Detallado (Uso Interno de TI)</label>
                            {{-- Contenedor para Quill --}}
                            <div id="editor" style="height: 380px;" class="theme-text">
                                {!! old('procedimiento_detallado', $ticket->solucion->procedimiento_detallado) !!}
                            </div>
                            {{-- Input oculto para enviar el HTML al servidor --}}
                            <input type="hidden" name="procedimiento_detallado" id="procedimiento_detallado">
                            @error('procedimiento_detallado') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Metadatos --}}
            <div class="col-lg-4">
                <div class="card card-premium shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold theme-text mb-3">Información del Caso</h6>
                        
                        <div class="p-3 rounded-3 mb-3 bg-light theme-bg-dark border border-secondary border-opacity-10">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Ticket Asociado</small>
                            <span class="theme-text fw-semibold d-block">#{{ $ticket->id_ticket }}</span>
                            <span class="theme-text text-muted small d-block">{{ Str::limit($ticket->asunto, 60) }}</span>
                        </div>
                        
                        <div class="p-3 rounded-3 mb-4 bg-light theme-bg-dark border border-secondary border-opacity-10">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Fecha de Cierre Original</small>
                            <span class="theme-text fw-semibold d-block">
                                <i class="bi bi-clock-history me-1 text-success"></i>
                                {{ $ticket->fecha_cierre ? \Carbon\Carbon::parse($ticket->fecha_cierre)->format('d/m/Y h:i A') : $ticket->updated_at->format('d/m/Y h:i A') }}
                            </span>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-2 shadow-sm mb-2 text-white fw-bold">
                            <i class="bi bi-save me-2"></i>Actualizar Solución
                        </button>
                        <a href="{{ route('soporte.tickets.tecnico.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                            Cancelar
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-info border-0 rounded-4 shadow-sm bg-info bg-opacity-10 text-info-emphasis p-3">
                    <small><i class="bi bi-info-circle-fill me-2"></i>Al actualizar esta solución, los datos en el historial operativo y en el centro de conocimientos se refrescarán al instante.</small>
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
        placeholder: 'Modifica el procedimiento técnico detallado...',
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
    .ql-toolbar { background: #fff !important; border-top-left-radius: 10px; border-top-right-radius: 10px; border-color: var(--bs-border-color) !important; }
    .ql-container { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; background: var(--bg-main); color: var(--text-main); font-size: 1rem; border-color: var(--bs-border-color) !important; }
</style>
@endsection
