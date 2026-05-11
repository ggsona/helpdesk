@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Cambiamos la ruta a actualizar y el método a PUT --}}
    <form action="{{ route('tecnico.tickets.actualizar-solucion', $ticket->id_ticket) }}" method="POST" id="formSolucion">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Panel Izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="fw-bold theme-text mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Editar Solución Técnica</h5>
                        <small class="text-muted">Modifica los detalles para mantener actualizada la Base de Conocimientos.</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label theme-text fw-bold">Título / Resumen para el Usuario</label>
                            <input type="text" name="resumen_usuario" class="form-control form-control-premium" 
                                   value="{{ old('resumen_usuario', $ticket->solucion->resumen_usuario) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label theme-text fw-bold">Procedimiento Detallado (Uso Interno)</label>
                            {{-- Contenedor para Quill --}}
                            <div id="editor" style="height: 400px;" class="theme-text">
                                {!! old('procedimiento_detallado', $ticket->solucion->procedimiento_detallado) !!}
                            </div>
                            {{-- Input oculto para enviar el HTML al servidor --}}
                            <input type="hidden" name="procedimiento_detallado" id="procedimiento_detallado">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Metadatos --}}
            <div class="col-lg-4">
                <div class="card card-premium shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold theme-text mb-3">Información de Referencia</h6>
                        <div class="p-3 rounded-3 mb-3" style="background: var(--bg-main);">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Ticket Original</small>
                            <span class="theme-text">#{{ $ticket->id_ticket }} - {{ $ticket->asunto }}</span>
                        </div>
                        <div class="p-3 rounded-3 mb-4" style="background: var(--bg-main);">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Fecha de Cierre</small>
                            <span class="theme-text">{{ $ticket->fecha_cierre ? \Carbon\Carbon::parse($ticket->fecha_cierre)->format('d/m/Y h:i A') : 'N/A' }}</span>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-2 shadow mb-2 text-white fw-bold">
                            <i class="bi bi-save me-2"></i>Actualizar Cambios
                        </button>
                        <a href="{{ route('tecnico.tickets.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                            Cancelar
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-info border-0 rounded-4 shadow-sm">
                    <small><i class="bi bi-info-circle me-2"></i>Recuerda que al editar esta solución, los cambios se verán reflejados inmediatamente en el historial y el blog técnico.</small>
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
        placeholder: 'Modifica el paso a paso de la solución...',
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
    .ql-toolbar { background: #fff !important; border-top-left-radius: 10px; border-top-right-radius: 10px; }
    .ql-container { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; background: var(--bg-main); color: var(--text-main); font-size: 1rem; }
    .form-control-premium:focus { border-color: var(--bs-warning); box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25); }
</style>
@endsection