@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 theme-text">
                    <i class="bi bi-tag-fill text-primary me-2"></i> Crear Nueva Categoría
                </h2>
                <p class="text-secondary mb-0">Crea una nueva clasificación para la tipificación de tickets.</p>
            </div>
            <a href="{{ route('admin.categorias.index') }}" class="btn btn-light rounded-3 px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="card card-premium shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.categorias.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-4">
                        <label for="nombre_categoria" class="form-label fw-semibold text-secondary small">Nombre de la Categoría</label>
                        <input type="text" class="form-control form-control-premium @error('nombre_categoria') is-invalid @enderror" id="nombre_categoria" name="nombre_categoria" value="{{ old('nombre_categoria') }}" placeholder="Ej. Soporte Software, Redes y Comunicaciones..." required autofocus>
                        @error('nombre_categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.categorias.index') }}" class="btn btn-light rounded-3 px-4 fw-bold">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">
                            <i class="bi bi-save me-2"></i> Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
