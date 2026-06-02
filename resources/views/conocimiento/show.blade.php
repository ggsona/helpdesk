@extends('layouts.admin')

@section('content')
<style>
    .article-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #374151;
    }
    .article-content h1, .article-content h2, .article-content h3 {
        color: #111827;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .article-content pre {
        background: #1e293b;
        color: #f8fafc;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
    }
    .attachment-card {
        border: 1px solid var(--bs-border-color, #e2e8f0);
        border-radius: 0.75rem;
        padding: 1rem;
        transition: all 0.2s ease;
        background: var(--bs-body-bg, white);
    }
    .attachment-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
    }
    .btn-feedback {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: transform 0.2s;
    }
    .btn-feedback:hover {
        transform: scale(1.1);
    }

    /* Soporte Dark Mode */
    [data-bs-theme="dark"] .article-content { color: #cbd5e1; }
    [data-bs-theme="dark"] .article-content h1, 
    [data-bs-theme="dark"] .article-content h2, 
    [data-bs-theme="dark"] .article-content h3 { color: #f8fafc; }
    [data-bs-theme="dark"] .attachment-card { background: #1e1e2d; border-color: #323248; }
    [data-bs-theme="dark"] .theme-title { color: #f8fafc !important; }

    /* ---- Estilos para impresión en PDF ---- */
    @media print {
        /* Ocultar toda la UI del sistema */
        nav, footer, aside, .breadcrumb, .btn-feedback,
        form[action*="valorar"], .attachment-card a,
        #print-btn-area, .alert, .badge,
        [data-bs-theme] nav, .sidebar, #kt_aside {
            display: none !important;
        }
        body { background: white !important; color: black !important; }
        .card { border: none !important; box-shadow: none !important; }
        .article-content { color: black !important; font-size: 12pt; line-height: 1.6; }
        .article-content h1, .article-content h2, .article-content h3 { color: black !important; }
        .article-content pre { border: 1px solid #ccc; background: #f5f5f5 !important; color: black !important; }
        .article-content img { max-width: 100% !important; }
        h1.fw-bold { color: black !important; font-size: 22pt; }
        /* Cabecera de impresión */
        .print-header { display: block !important; }
        /* Pie de página con URL */
        @page { margin: 2cm; }
    }
    /* El encabezado de impresión está oculto en pantalla */
    .print-header { display: none; }
</style>

<div class="container py-4" style="max-width: 900px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soporte.conocimiento.index') }}" class="text-decoration-none">Base de Conocimiento</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soporte.conocimiento.index', ['categoria' => $articulo->id_categoria]) }}" class="text-decoration-none">{{ $articulo->categoria->nombre_categoria ?? 'General' }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Leer</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header del Artículo -->
    <div class="mb-5 text-center">
        @if($articulo->es_destacado)
            <span class="badge bg-warning text-dark rounded-pill px-3 mb-3"><i class="bi bi-star-fill me-1"></i> Artículo Destacado</span>
        @endif
        
        <h1 class="fw-bold mb-4 theme-title" style="font-size: 2.5rem; color: #1e293b;">{{ $articulo->titulo }}</h1>
        
        <div class="d-flex justify-content-center align-items-center gap-4 text-muted flex-wrap">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-2" style="width: 35px; height: 35px; font-weight: bold;">
                    {{ substr($articulo->autor->name ?? 'U', 0, 1) }}
                </div>
                <span class="fw-medium text-body">{{ $articulo->autor->name ?? 'Usuario' }}</span>
            </div>
            <span><i class="bi bi-calendar3 me-1"></i> {{ $articulo->fecha_publicacion ? $articulo->fecha_publicacion->format('d de M, Y') : 'Borrador' }}</span>
            <span><i class="bi bi-eye me-1"></i> {{ $articulo->vistas }} vistas</span>
        </div>

        @if($articulo->tags->count() > 0)
            <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                @foreach($articulo->tags as $tag)
                    <a href="{{ route('soporte.conocimiento.index', ['tag' => $tag->nombre]) }}" class="text-decoration-none">
                        <span class="badge rounded-pill" style="background-color: {{ $tag->estado ? $tag->color.'20' : '#e9ecef' }}; color: {{ $tag->estado ? $tag->color : '#6c757d' }}; border: 1px solid {{ $tag->estado ? $tag->color.'40' : '#ced4da' }}; font-weight: 600; padding: 0.5em 1em; transition: all 0.2s ease;" title="{{ !$tag->estado ? 'Etiqueta inactiva' : '' }}" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            #{{ $tag->nombre }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Origen del ticket (si aplica) -->
    @if($articulo->origen == 'ticket' && $articulo->solucion)
        <div class="alert alert-info border-info-subtle bg-info-subtle text-info-emphasis d-flex align-items-center rounded-3 shadow-sm mb-5 p-4">
            <i class="bi bi-info-circle-fill fs-3 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Solución extraída de un Ticket Real</h6>
                <p class="mb-0 small">Este artículo fue documentado por el equipo de soporte tras resolver el ticket <strong>#{{ str_pad($articulo->solucion->ticket->id_ticket, 5, '0', STR_PAD_LEFT) }}</strong>.</p>
            </div>
        </div>
    @endif

    <!-- Contenido Principal -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4 p-md-5">
            <div class="article-content">
                {!! $articulo->contenido !!}
            </div>
        </div>
    </div>

    <!-- Herramientas y Adjuntos -->
    @if($articulo->adjuntos->count() > 0)
    <div class="mb-5">
        <h4 class="fw-bold mb-3"><i class="bi bi-box-seam text-primary me-2"></i> Herramientas y Archivos</h4>
        <div class="row g-3">
            @foreach($articulo->adjuntos as $adjunto)
                <div class="col-12 col-md-6">
                    <div class="attachment-card d-flex align-items-center">
                        <div class="bg-light text-primary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                            <i class="bi bi-file-earmark-zip"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="fw-bold mb-0 text-truncate" title="{{ $adjunto->nombre_original }}">{{ $adjunto->nombre_original }}</h6>
                            <small class="text-muted">{{ number_format($adjunto->tamano / 1048576, 2) }} MB • {{ $adjunto->descargas }} descargas</small>
                        </div>
                        <a href="{{ route('soporte.conocimiento.descargar', $adjunto->id) }}" class="btn btn-outline-primary btn-sm rounded-pill ms-2 fw-bold px-3">
                            <i class="bi bi-download me-1"></i> Bajar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Valoración y Feedback -->
    <div class="card border-0 rounded-4 text-center p-5 mb-5 shadow-sm border-light-subtle bg-body-secondary">
        <h5 class="fw-bold mb-3">¿Te resultó útil este artículo?</h5>
        <form action="{{ route('soporte.conocimiento.valorar', $articulo->id_articulo) }}" method="POST" class="d-flex justify-content-center gap-3">
            @csrf
            <button type="submit" name="es_util" value="1" class="btn btn-white shadow-sm btn-feedback border text-success">
                <i class="bi bi-hand-thumbs-up-fill"></i>
            </button>
            <button type="submit" name="es_util" value="0" class="btn btn-white shadow-sm btn-feedback border text-danger">
                <i class="bi bi-hand-thumbs-down-fill"></i>
            </button>
        </form>
    </div>

    <!-- Acciones del Artículo -->
    <div id="print-btn-area" class="d-flex justify-content-center gap-3 mb-5 flex-wrap">

        @can('editar-articulo')
        <a href="{{ route('soporte.conocimiento.edit', $articulo->slug) }}"
           class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="bi bi-pencil-square me-2"></i> Editar
        </a>
        @endcan

        @can('archivar-articulo')
        @if($articulo->estado !== 'archivado')
        <form action="{{ route('soporte.conocimiento.archivar', $articulo->id_articulo) }}" method="POST"
              onsubmit="return confirm('¿Archivar este artículo? Quedará oculto de la vista pública pero podrás restaurarlo.')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-warning rounded-pill px-4 fw-bold">
                <i class="bi bi-archive-fill me-2"></i> Archivar
            </button>
        </form>
        @else
        <form action="{{ route('soporte.conocimiento.update', $articulo->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="titulo" value="{{ $articulo->titulo }}">
            <input type="hidden" name="contenido" value="{{ $articulo->contenido }}">
            <input type="hidden" name="id_categoria" value="{{ $articulo->id_categoria }}">
            <input type="hidden" name="estado" value="publicado">
            <button type="submit" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-up-circle-fill me-2"></i> Restaurar
            </button>
        </form>
        @endif
        @endcan

        @can('imprimir-articulo')
        <button onclick="imprimirArticulo()" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
            <i class="bi bi-printer-fill me-2"></i> Imprimir / PDF
        </button>
        @endcan

        {{-- Solo Admin: eliminación permanente --}}
        @can('eliminar-articulo')
        <form action="{{ route('soporte.conocimiento.destroy', $articulo->id_articulo) }}" method="POST"
              onsubmit="return confirm('⚠️ ELIMINAR PERMANENTEMENTE: Esta acción no se puede deshacer. ¿Estás seguro?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                <i class="bi bi-trash3-fill me-2"></i> Eliminar
            </button>
        </form>
        @endcan

    </div>
</div>

<script>
function imprimirArticulo() {
    // Añadir clase temporal para activar estilos de impresión
    document.body.classList.add('printing');
    window.print();
    document.body.classList.remove('printing');
}
</script>
@endsection
