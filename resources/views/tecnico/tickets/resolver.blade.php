@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <form action="{{ route('tecnico.tickets.guardar-solucion', $ticket->id_ticket) }}" method="POST" id="formSolucion">
        @csrf
        <div class="row">
            {{-- Panel Izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="fw-bold theme-text mb-0">Documentar Solución Técnica</h5>
                        <small class="text-muted">Este contenido alimentará la Base de Conocimientos.</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label theme-text fw-bold">Título / Resumen para el Usuario</label>
                            <input type="text" name="resumen_usuario" class="form-control form-control-premium" 
                                   placeholder="Ej: Reemplazo de memoria RAM y limpieza de slots" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label theme-text fw-bold">Procedimiento Detallado (Uso Interno)</label>
                            {{-- Contenedor para Quill --}}
                            <div id="editor" style="height: 400px;" class="theme-text"></div>
                            {{-- Input oculto para enviar el HTML al servidor --}}
                            <input type="hidden" name="procedimiento_detallado" id="procedimiento_detallado">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Metadatos (Como en tu imagen) --}}
            <div class="col-lg-4">
                <div class="card card-premium shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold theme-text mb-3">Detalles del Ticket</h6>
                        <div class="p-3 rounded-3 mb-3" style="background: var(--bg-main);">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Ticket</small>
                            <span class="theme-text">#{{ $ticket->id_ticket }} - {{ $ticket->asunto }}</span>
                        </div>
                        <div class="p-3 rounded-3 mb-4" style="background: var(--bg-main);">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Usuario</small>
                            <span class="theme-text">{{ $ticket->usuario->name }}</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow mb-2">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Finalizar
                        </button>
                        <a href="{{ route('tecnico.tickets.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
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
        placeholder: 'Describe paso a paso cómo lo arreglaste...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'], // Permite insertar imágenes
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
    /* Ajuste para que Quill se vea bien en modo oscuro si lo usas */
    .ql-toolbar { background: #fff !important; border-top-left-radius: 10px; border-top-right-radius: 10px; }
    .ql-container { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; background: var(--bg-main); color: var(--text-main); }
</style>
@endsection