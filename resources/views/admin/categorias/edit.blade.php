@extends("layouts.admin")

@section("content")
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Editar Categoría</h1>

        <div class="card shadow mb-4 card-premium">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Formulario de Edición</h6>
            </div>
            <div class="card-body">
                <form action="{{ route("admin.categorias.update", $categoria->id_categoria) }}" method="POST">
                    @csrf
                    @method("PUT")
                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control @error("nombre_categoria") is-invalid @enderror" id="nombre_categoria" name="nombre_categoria" value="{{ old("nombre_categoria", $categoria->nombre_categoria) }}" required autofocus>
                        @error("nombre_categoria")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select @error("estado") is-invalid @enderror" id="estado" name="estado" required>
                            <option value="1" {{ old("estado", $categoria->estado) == 1 ? "selected" : "" }}>Activa</option>
                            <option value="0" {{ old("estado", $categoria->estado) == 0 ? "selected" : "" }}>Inactiva</option>
                        </select>
                        @error("estado")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <a href="{{ route("admin.categorias.index") }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
                </form>
            </div>
        </div>
    </div>
@endsection
