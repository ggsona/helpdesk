@extends('layouts.admin')

@section('content')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 theme-text">
                <i class="bi bi-pencil-square text-primary me-2"></i> Editar Artículo
            </h2>
            <p class="text-secondary mb-0">Modificando: {{ $articulo->titulo }}</p>
        </div>
        <a href="{{ route('soporte.conocimiento.show', $articulo->slug) }}" class="btn btn-outline-secondary rounded-pill fw-bold px-3">
            <i class="bi bi-arrow-left"></i> Cancelar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 rounded-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('soporte.conocimiento.update', $articulo->slug) }}" method="POST" enctype="multipart/form-data" id="article-form">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Columna Principal -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Título del Artículo</label>
                            <input type="text" name="titulo" class="form-control form-control-lg fw-bold" value="{{ old('titulo', $articulo->titulo) }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Contenido Completo</label>
                            <!-- Contenedor del Editor Quill -->
                            <div id="editor-container" style="height: 400px; border-radius: 0 0 0.5rem 0.5rem;" class="bg-body text-body border-light-subtle">{!! old('contenido', $articulo->contenido) !!}</div>
                            <!-- Input Oculto para enviar el HTML -->
                            <input type="hidden" name="contenido" id="contenido">
                        </div>
                    </div>
                </div>

                @if($articulo->adjuntos->count() > 0)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-paperclip me-2"></i> Adjuntos Actuales</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($articulo->adjuntos as $adjunto)
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                                    <div>
                                        <i class="bi bi-file-earmark-zip text-primary me-2"></i> 
                                        <strong>{{ $adjunto->nombre_original }}</strong> 
                                        <span class="text-muted small ms-2">({{ number_format($adjunto->tamano / 1048576, 2) }} MB)</span>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $adjunto->descargas }} descargas</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="alert alert-warning mt-3 mb-0 small py-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Por el momento, para eliminar un adjunto debes comunicarte con el administrador de base de datos. Los archivos que subas nuevos se sumarán a la lista.
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Columna Lateral -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Clasificación y Estado</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Categoría Principal</label>
                            <select name="id_categoria" class="form-select border-light-subtle shadow-none">
                                <option value="">General</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria }}" {{ old('id_categoria', $articulo->id_categoria) == $cat->id_categoria ? 'selected' : '' }}>{{ $cat->nombre_categoria }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Etiquetas (Tags)</label>
                            @php
                                $selectedTags = is_array(old('tags')) ? old('tags') : $articulo->tags->pluck('nombre')->toArray();
                            @endphp
                            <select name="tags[]" id="tags-select" multiple class="form-select">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->nombre }}" {{ in_array($tag->nombre, $selectedTags) ? 'selected' : '' }}>
                                        {{ $tag->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Estado de Publicación</label>
                            <select name="estado" class="form-select border-light-subtle shadow-none">
                                <option value="publicado" {{ old('estado', $articulo->estado) == 'publicado' ? 'selected' : '' }}>Publicado (Visible ahora)</option>
                                <option value="borrador" {{ old('estado', $articulo->estado) == 'borrador' ? 'selected' : '' }}>Borrador (Solo tú lo ves)</option>
                                <option value="archivado" {{ old('estado', $articulo->estado) == 'archivado' ? 'selected' : '' }}>Archivado (Desactualizado)</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="es_destacado" name="es_destacado" {{ old('es_destacado', $articulo->es_destacado) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold" for="es_destacado">Fijar como Destacado</label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="es_interno" name="es_interno" {{ old('es_interno', $articulo->es_interno) ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="es_interno">Solo visible para Técnicos</label>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Subir más Herramientas</h6>
                        <p class="small text-muted mb-2">Sube ejecutables adicionales o manuales. Máx {{ env('KB_MAX_UPLOAD_KB', 1048576) / 1024 }} MB.</p>
                        <input class="form-control form-control-sm" type="file" name="adjuntos[]" multiple>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm fs-5">
                    <i class="bi bi-save-fill me-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<style>
    /* Ajustes para que Choices encaje con nuestro tema */
    .choices__inner { border-radius: 0.5rem; background-color: #fff; border-color: #dee2e6; color: #212529; }
    .choices__list--multiple .choices__item { background-color: #4f46e5; border: none; border-radius: 50rem; }
    .choices[data-type*=select-multiple] .choices__button { border-left: none; }
    .choices__list--dropdown { z-index: 1050; }
    
    /* Soporte Dark Mode */
    [data-bs-theme="dark"] .choices__inner { background-color: #1e1e2d; border-color: #323248; color: #fff; }
    [data-bs-theme="dark"] .choices__input { background-color: transparent; color: #fff; }
    [data-bs-theme="dark"] .choices__list--dropdown { background-color: #1e1e2d; border-color: #323248; color: #fff; }
    [data-bs-theme="dark"] .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: #2b2b40; color: #fff; }
    [data-bs-theme="dark"] .choices__list--dropdown .choices__item--selectable { color: #ccc; }
    /* Soporte Dark Mode para Quill Editor */
    [data-bs-theme="dark"] .ql-toolbar.ql-snow { border-color: #323248; background-color: #1e1e2d; }
    [data-bs-theme="dark"] .ql-container.ql-snow { border-color: #323248; background-color: #1e1e2d; color: #fff; }
    [data-bs-theme="dark"] .ql-snow .ql-stroke { stroke: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-fill { fill: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-picker { color: #ccc; }
    [data-bs-theme="dark"] .ql-snow .ql-picker-options { background-color: #1e1e2d; border-color: #323248; }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar Quill Editor
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Redacta el diagnóstico, procedimiento y causa raíz aquí...'
        });

        // Al enviar el formulario, inyectar el HTML de Quill en el input oculto
        var form = document.getElementById('article-form');
        form.onsubmit = function() {
            var html = quill.root.innerHTML;
            if (html === '<p><br></p>') {
                alert("El contenido no puede estar vacío.");
                return false;
            }
            document.getElementById('contenido').value = html;
        };

        // Inicializar Choices.js
        const element = document.getElementById('tags-select');
        const choices = new Choices(element, {
            removeItemButton: true,
            maxItemCount: 4,
            searchResultLimit: 10,
            renderChoiceLimit: -1,
            placeholder: true,
            placeholderValue: 'Selecciona hasta 4 etiquetas',
            noResultsText: 'No se encontraron etiquetas',
            noChoicesText: 'No hay más etiquetas',
            itemSelectText: 'Haz clic para seleccionar'
        });
    });
</script>
@endsection
