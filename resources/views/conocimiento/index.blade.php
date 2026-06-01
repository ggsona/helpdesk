@extends('layouts.admin')

@section('content')
<style>
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease;
    }
    .hero-kb {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
        border-radius: 1rem;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
    }
</style>

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="hero-kb text-center shadow-sm">
        @if(request('estado') === 'archivados')
            <h1 class="fw-bold mb-3"><i class="bi bi-archive-fill me-2 text-warning"></i> Archivo de Conocimiento</h1>
            <p class="lead mb-4 opacity-75">Consultando artículos inactivos o antiguos.</p>
        @elseif(request('tag'))
            <h1 class="fw-bold mb-3"><i class="bi bi-hash me-1"></i>{{ request('tag') }}</h1>
            <p class="lead mb-4 opacity-75">Artículos etiquetados con #{{ request('tag') }}</p>
        @else
            <h1 class="fw-bold mb-3"><i class="bi bi-journal-bookmark-fill me-2"></i> Base de Conocimiento</h1>
            <p class="lead mb-4 opacity-75">Encuentra soluciones, manuales, procedimientos y herramientas compartidas por el equipo técnico.</p>
        @endif
        
        <form action="{{ route('soporte.conocimiento.index') }}" method="GET" class="d-flex justify-content-center">
            @if(request('estado')) <input type="hidden" name="estado" value="{{ request('estado') }}"> @endif
            @if(request('tag')) <input type="hidden" name="tag" value="{{ request('tag') }}"> @endif
            @if(request('categoria')) <input type="hidden" name="categoria" value="{{ request('categoria') }}"> @endif

            <div class="input-group" style="max-width: 600px;">
                <input type="text" name="q" class="form-control form-control-lg border-0 shadow-sm search-premium" placeholder="¿Qué estás buscando...?" value="{{ request('q') }}">
                <button class="btn btn-light px-4 shadow-sm text-primary fw-bold" type="submit"><i class="bi bi-search"></i> Buscar</button>
            </div>
        </form>
    </div>

    <!-- Actions & Categories -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--bg-main);" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-2"></i> 
                @if(request('categoria'))
                    {{ $categorias->where('id_categoria', request('categoria'))->first()->nombre_categoria ?? 'Categoría Desconocida' }}
                @else
                    Todas las Categorías
                @endif
            </button>
            <ul class="dropdown-menu shadow border-0" style="max-height: 300px; overflow-y: auto; background-color: var(--bg-main);">
                <li>
                    <a class="dropdown-item {{ !request('categoria') ? 'active' : '' }} theme-text" 
                       href="{{ route('soporte.conocimiento.index', array_merge(request()->except('categoria'))) }}">
                        Todas las Categorías
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach($categorias as $cat)
                    <li>
                        <a class="dropdown-item {{ request('categoria') == $cat->id_categoria ? 'active' : '' }} theme-text" 
                           href="{{ route('soporte.conocimiento.index', array_merge(request()->query(), ['categoria' => $cat->id_categoria])) }}">
                            {{ $cat->nombre_categoria }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
        @can('crear-articulo')
        <a href="{{ route('soporte.conocimiento.create') }}" class="btn btn-primary rounded-pill fw-bold shadow-sm px-4">
            <i class="bi bi-pencil-square me-2"></i> Nuevo Artículo
        </a>
        @endcan
    </div>

    <!-- Resultados / Listado -->
    @if($articulos->isEmpty())
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-50" alt="Vacio">
            <h4 class="text-secondary fw-bold">No encontramos artículos</h4>
            <p class="text-muted">Intenta buscar con otros términos o en otra categoría.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($articulos as $art)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 card-hover rounded-4 overflow-hidden">

                    
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-3 d-flex justify-content-between align-items-start gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 text-wrap text-start">
                                {{ $art->categoria->nombre_categoria ?? 'General' }}
                            </span>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if($art->es_destacado)
                                    <span class="badge bg-warning text-dark rounded-pill p-2 shadow-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Artículo Destacado">
                                        <i class="bi bi-star-fill"></i>
                                    </span>
                                @endif
                                @if($art->origen == 'ticket')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill p-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Originado desde un Ticket">
                                        <i class="bi bi-ticket-detailed"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <h4 class="card-title fw-bold mb-2 theme-text" style="line-height: 1.3;">
                            <a href="{{ route('soporte.conocimiento.show', $art->slug) }}" class="text-decoration-none text-reset stretched-link">
                                {{ $art->titulo }}
                            </a>
                        </h4>
                        
                        <p class="card-text text-muted small flex-grow-1 mb-4">
                            {{ Str::limit($art->extracto, 120) }}
                        </p>
                        
                        <!-- Footer del Card -->
                        <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: bold;">
                                    {{ substr($art->autor->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="ms-2 lh-1">
                                    <div class="small fw-bold">{{ $art->autor->name ?? 'Usuario' }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $art->fecha_publicacion ? $art->fecha_publicacion->format('d M, Y') : 'Borrador' }}</div>
                                </div>
                            </div>
                            <div class="text-muted small">
                                <span class="me-3" title="Vistas"><i class="bi bi-eye"></i> {{ $art->vistas }}</span>
                                @if($art->adjuntos->count() > 0)
                                    <span title="Adjuntos"><i class="bi bi-paperclip"></i> {{ $art->adjuntos->count() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            {{ $articulos->links() }}
        </div>
    @endif
</div>

<!-- Inicializar Tooltips de Bootstrap -->
<script>
    document.addEventListener("DOMContentLoaded", function(){
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection
