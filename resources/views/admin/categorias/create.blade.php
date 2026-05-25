@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Crear Nueva Categoría</h1>

        <div class="card shadow mb-4 card-premium">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Formulario de Creación</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categorias.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control @error('nombre_categoria') is-invalid @enderror" id="nombre_categoria" name="nombre_categoria" value="{{ old('nombre_categoria') }}" required autofocus>
                        @error('nombre_categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <a href="{{ route('admin.categorias.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </form>
            </div>
        </div>
    </div>
@endsection
