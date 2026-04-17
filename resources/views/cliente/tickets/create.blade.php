<x-cliente-layout>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('cliente.tickets.index') }}" class="text-decoration-none small text-muted hover-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista de tickets
            </a>
            <h2 class="fw-bold theme-text mt-3">Crear Nueva Solicitud</h2>
            <p class="text-muted">Por favor, detalla el inconveniente. Puedes adjuntar fotos o videos como referencia.</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card-premium shadow-sm p-4 mb-4">
                    <form action="{{ route('cliente.tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold theme-text">Asunto del Ticket</label>
                                <input type="text" name="asunto" 
                                       class="form-control form-control-premium @error('asunto') is-invalid @enderror" 
                                       placeholder="Resumen corto (ej: Mi laptop no enciende)" 
                                       value="{{ old('asunto') }}" required>
                                @error('asunto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold theme-text">Categoría</label>
                                <select name="id_categoria" class="form-select form-control-premium shadow-none" required>
                                    <option value="" selected disabled>Selecciona una opción</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria }}">{{ $cat->nombre_categoria }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold theme-text">Equipo afectado</label>
                                <select name="id_tipo_equipo" class="form-select form-control-premium shadow-none" required>
                                    <option value="" selected disabled>Selecciona el equipo</option>
                                    @foreach($tiposEquipo as $tipo)
                                        <option value="{{ $tipo->id_tipo_equipo }}">{{ $tipo->nombre_tipo_equipo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold theme-text">Descripción del problema</label>
                                <textarea name="descripcion_problema" rows="5" 
                                          class="form-control form-control-premium @error('descripcion_problema') is-invalid @enderror" 
                                          placeholder="Cuéntanos más detalles sobre el fallo..." required>{{ old('descripcion_problema') }}</textarea>
                                @error('descripcion_problema') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold theme-text">Archivos Adjuntos (Opcional)</label>
                                <div class="upload-container border border-dashed rounded p-4 text-center bg-light theme-bg-dark border-primary-subtle">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-2 d-block"></i>
                                    <input type="file" name="adjuntos[]" id="adjuntos" 
                                           class="form-control shadow-none border-0 bg-transparent" 
                                           multiple accept="image/*,video/mp4,application/pdf">
                                    <p class="small text-muted mt-2 mb-0">
                                        Arrastra o selecciona tus fotos, videos o documentos (Máx. 10MB c/u).
                                    </p>
                                </div>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <hr class="my-4 opacity-25">
                                <button type="reset" class="btn btn-light rounded-pill px-4 me-2">Cancelar</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                                    <i class="bi bi-check2-circle me-1"></i> Enviar Ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-premium bg-primary text-white p-4 shadow-sm border-0 mb-4">
                    <h5 class="fw-bold"><i class="bi bi-lightbulb me-2"></i>¿Sabías que?</h5>
                    <p class="small opacity-75 mb-0">
                        Adjuntar una captura de pantalla del error ayuda a nuestros técnicos a resolver tu problema hasta un 40% más rápido.
                    </p>
                </div>
                
                <div class="card-premium p-4 shadow-sm">
                    <h6 class="fw-bold theme-text">Formatos permitidos:</h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="bi bi-file-image me-2 text-primary"></i> Imágenes (JPG, PNG)</li>
                        <li class="mb-2"><i class="bi bi-file-earmark-play me-2 text-primary"></i> Videos (MP4)</li>
                        <li><i class="bi bi-file-earmark-pdf me-2 text-primary"></i> Documentos (PDF, TXT)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-cliente-layout>