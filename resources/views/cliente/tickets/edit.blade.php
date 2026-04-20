<x-cliente-layout>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('cliente.tickets.index') }}" class="btn btn-link text-decoration-none p-0 text-muted">
                <i class="bi bi-arrow-left"></i> Cancelar y volver
            </a>
            <h2 class="fw-bold mt-3">Editar Borrador #{{ $ticket->id_ticket }}</h2>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('cliente.tickets.update', $ticket->id_ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Asunto del Problema</label>
                                <input type="text" name="asunto" class="form-control" value="{{ old('asunto', $ticket->asunto) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción Detallada</label>
                                <textarea name="descripcion_problema" class="form-control" rows="5">{{ old('descripcion_problema', $ticket->descripcion_problema) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Categoría</label>
                                <select name="id_categoria" class="form-select" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria }}" {{ $ticket->id_categoria == $cat->id_categoria ? 'selected' : '' }}>
                                            {{ $cat->nombre_categoria }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Añadir más archivos (Opcional)</label>
                                <input type="file" name="archivos[]" class="form-control" multiple>
                                <small class="text-muted mt-2 d-block">Puedes subir imágenes o documentos PDF.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold py-2">
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                                <button type="reset" class="btn btn-light border py-2">Deshacer</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-cliente-layout>