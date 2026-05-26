@extends("layouts.admin")

@section("content")
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 theme-text">
                    <i class="bi bi-tag-fill text-primary me-2"></i> Editar Categoría
                </h2>
                <p class="text-secondary mb-0">Modifica los detalles de la clasificación seleccionada.</p>
            </div>
            <a href="{{ route("admin.categorias.index") }}" class="btn btn-light rounded-3 px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="card card-premium shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route("admin.categorias.update", $categoria->id_categoria) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method("PUT")
                    <div class="mb-4">
                        <label for="nombre_categoria" class="form-label fw-semibold text-secondary small">Nombre de la Categoría</label>
                        <input type="text" class="form-control form-control-premium @error("nombre_categoria") is-invalid @enderror" id="nombre_categoria" name="nombre_categoria" value="{{ old("nombre_categoria", $categoria->nombre_categoria) }}" required autofocus>
                        @error("nombre_categoria")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="estado" class="form-label fw-semibold text-secondary small">Estado</label>
                        <select class="form-select form-select-premium @error("estado") is-invalid @enderror" id="estado" name="estado" required>
                            <option value="1" {{ old("estado", $categoria->estado) == 1 ? "selected" : "" }}>Activa</option>
                            <option value="0" {{ old("estado", $categoria->estado) == 0 ? "selected" : "" }}>Inactiva</option>
                        </select>
                        @error("estado")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route("admin.categorias.index") }}" class="btn btn-light rounded-3 px-4 fw-bold">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">
                            <i class="bi bi-arrow-repeat me-2"></i> Actualizar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
