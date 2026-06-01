@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 theme-text"><i class="bi bi-tags-fill text-primary me-2"></i>Gestión de Etiquetas (Tags)</h3>
            <p class="text-secondary mb-0">Crea, edita y organiza las etiquetas utilizadas en la base de conocimiento.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 rounded-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Formulario Nuevo Tag -->
        <div class="col-12 col-md-4">
            <div class="card card-premium shadow-sm border-0 rounded-4">
                <div class="card-header bg-transparent border-bottom p-3">
                    <h5 class="fw-bold theme-text mb-0">Nueva Etiqueta</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('soporte.tags.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold theme-text small">Nombre de la Etiqueta</label>
                            <input type="text" name="nombre" class="form-control form-control-premium" placeholder="Ej: vpn, impresoras..." required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold theme-text small">Color de la Etiqueta</label>
                            <input type="color" name="color" class="form-control form-control-color w-100 form-control-premium" value="{{ sprintf('#%06X', mt_rand(0, 0xFFFFFF)) }}" style="height: 40px;" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-plus-circle me-2"></i>Crear Etiqueta
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Listado de Tags -->
        <div class="col-12 col-md-8">
            <div class="card card-premium shadow-sm border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 theme-text">
                            <thead class="bg-light theme-bg-dark">
                                <tr>
                                    <th class="ps-4">Vista Previa</th>
                                    <th>Nombre (Slug)</th>
                                    <th class="text-center">Artículos Relacionados</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $tag)
                                    <tr>
                                        <td class="ps-4 text-nowrap">
                                            <span class="badge rounded-pill" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40; font-weight: 600; padding: 0.5em 1em;">
                                                #{{ $tag->nombre }}
                                            </span>
                                            @if(!$tag->estado)
                                                <span class="badge bg-danger ms-2"><i class="bi bi-eye-slash"></i> Inactiva</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $tag->nombre }}</div>
                                            <div class="text-muted small">{{ $tag->slug }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">{{ $tag->articulos_count }}</span>
                                        </td>
                                        <td class="text-end pe-4 text-nowrap">
                                            <div class="d-flex flex-row justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#editTagModal{{ $tag->id }}" title="Editar etiqueta">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                @if($tag->articulos_count > 0 || !$tag->estado)
                                                    <form action="{{ route('soporte.tags.toggle', $tag->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm {{ $tag->estado ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill" title="{{ $tag->estado ? 'Desactivar etiqueta' : 'Activar etiqueta' }}">
                                                            <i class="bi {{ $tag->estado ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('soporte.tags.destroy', $tag->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta etiqueta de forma permanente?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Eliminar etiqueta">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Edición -->
                                    <div class="modal fade" id="editTagModal{{ $tag->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content card-premium border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold theme-text">Editar Etiqueta</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('soporte.tags.update', $tag->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold theme-text small">Nombre de la Etiqueta</label>
                                                            <input type="text" name="nombre" class="form-control form-control-premium" value="{{ $tag->nombre }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold theme-text small">Color de la Etiqueta</label>
                                                            <input type="color" name="color" class="form-control form-control-color w-100 form-control-premium" value="{{ $tag->color }}" style="height: 40px;" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay etiquetas creadas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
