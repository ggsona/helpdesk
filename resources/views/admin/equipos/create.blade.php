@extends('layouts.admin')

@section('content')
    <div class="py-3 px-1">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 theme-text">
                    <i class="bi bi-pc-display text-primary me-2"></i> Registrar Nuevo Equipo
                </h2>
                <p class="text-secondary mb-0">Agrega un nuevo elemento al inventario y configúralo.</p>
            </div>
            <a href="{{ route('admin.equipos.index') }}" class="btn btn-light rounded-3 px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="card card-premium shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.equipos.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    <div class="row">
                        <!-- Nombre del Equipo -->
                        <div class="col-md-6 mb-4">
                            <label for="nombre" class="form-label fw-semibold text-secondary small">Nombre del Equipo o Dispositivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-premium @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. PC de Escritorio - Contabilidad, Teclado Mecánico..." required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Número de Bien Institucional -->
                        <div class="col-md-6 mb-4">
                            <label for="numero_bien" class="form-label fw-semibold text-secondary small">Número de Bien Institucional (Código de Barra/Etiq.)</label>
                            <input type="text" class="form-control form-control-premium @error('numero_bien') is-invalid @enderror" id="numero_bien" name="numero_bien" value="{{ old('numero_bien') }}" placeholder="Ej. GDC-EQ-2026-004">
                            @error('numero_bien')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tipo de Equipo -->
                        <div class="col-md-4 mb-4">
                            <label for="id_tipo_equipo" class="form-label fw-semibold text-secondary small">Tipo de Dispositivo <span class="text-danger">*</span></label>
                            <select class="form-select form-select-premium @error('id_tipo_equipo') is-invalid @enderror" id="id_tipo_equipo" name="id_tipo_equipo" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_equipo }}" {{ old('id_tipo_equipo') == $tipo->id_tipo_equipo ? 'selected' : '' }}>
                                        {{ $tipo->nombre_tipo_equipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo_equipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Marca (Select Relacional) -->
                        <div class="col-md-4 mb-4">
                            <label for="id_marca" class="form-label fw-semibold text-secondary small">Marca / Fabricante</label>
                            <select class="form-select form-select-premium @error('id_marca') is-invalid @enderror" id="id_marca" name="id_marca">
                                <option value="">-- Seleccionar Marca --</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id_marca }}" {{ old('id_marca') == $marca->id_marca ? 'selected' : '' }}>{{ $marca->nombre_marca }}</option>
                                @endforeach
                            </select>
                            @error('id_marca')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Modelo (Select Dinámico) -->
                        <div class="col-md-4 mb-4">
                            <label for="id_modelo" class="form-label fw-semibold text-secondary small">Modelo</label>
                            <select class="form-select form-select-premium @error('id_modelo') is-invalid @enderror" id="id_modelo" name="id_modelo">
                                <option value="">-- Primero selecciona una marca --</option>
                            </select>
                            @error('id_modelo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Especificaciones de Red y Hardware (Campos Dinámicos) -->
                    <div id="seccion-tecnica" style="display: none;">
                        <h5 class="fw-bold mb-3 text-primary mt-2"><i class="bi bi-gear-wide-connected me-2"></i>Especificaciones Técnicas y de Red</h5>
                        <div class="row">
                            <!-- Dirección IP -->
                            <div class="col-md-6 mb-4">
                                <label for="ip_address" class="form-label fw-semibold text-secondary small">Dirección IP</label>
                                <input type="text" class="form-control form-control-premium @error('ip_address') is-invalid @enderror" id="ip_address" name="ip_address" value="{{ old('ip_address') }}" placeholder="Ej. 192.168.1.50">
                                @error('ip_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dirección MAC -->
                            <div class="col-md-6 mb-4">
                                <label for="mac_address" class="form-label fw-semibold text-secondary small">Dirección MAC</label>
                                <input type="text" class="form-control form-control-premium @error('mac_address') is-invalid @enderror" id="mac_address" name="mac_address" value="{{ old('mac_address') }}" placeholder="Ej. 00:1A:2B:3C:4D:5E">
                                @error('mac_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Procesador -->
                            <div class="col-md-4 mb-4">
                                <label for="procesador" class="form-label fw-semibold text-secondary small">Procesador / CPU</label>
                                <input type="text" class="form-control form-control-premium @error('procesador') is-invalid @enderror" id="procesador" name="procesador" value="{{ old('procesador') }}" placeholder="Ej. Intel Core i7, AMD Ryzen 5...">
                                @error('procesador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Memoria RAM -->
                            <div class="col-md-4 mb-4">
                                <label for="ram" class="form-label fw-semibold text-secondary small">Memoria RAM</label>
                                <input type="text" class="form-control form-control-premium @error('ram') is-invalid @enderror" id="ram" name="ram" value="{{ old('ram') }}" placeholder="Ej. 8 GB, 16 GB, 32 GB...">
                                @error('ram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Disco Duro / Almacenamiento -->
                            <div class="col-md-4 mb-4">
                                <label for="disco_duro" class="form-label fw-semibold text-secondary small">Disco Duro / Almacenamiento</label>
                                <input type="text" class="form-control form-control-premium @error('disco_duro') is-invalid @enderror" id="disco_duro" name="disco_duro" value="{{ old('disco_duro') }}" placeholder="Ej. 512 GB SSD NVMe, 1 TB HDD...">
                                @error('disco_duro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Usuario Asignado (Con buscador Select2) -->
                        <div class="col-md-6 mb-4">
                            <label for="id_usuario_asignado" class="form-label fw-semibold text-secondary small">Usuario Asignado Responsable</label>
                            <select class="form-select form-select-premium @error('id_usuario_asignado') is-invalid @enderror" id="id_usuario_asignado" name="id_usuario_asignado">
                                <option value="">-- Sin Asignar / Disponible en Inventario --</option>
                                @foreach($usuarios as $usr)
                                    <option value="{{ $usr->id }}" {{ old('id_usuario_asignado') == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ $usr->email }})
                                        @if($usr->persona->id_unidad_administrativa)
                                            — {{ $usr->persona->unidadAdministrativa->nombre }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('id_usuario_asignado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6 mb-4">
                            <label for="estado" class="form-label fw-semibold text-secondary small">Estado Operativo <span class="text-danger">*</span></label>
                            <select class="form-select form-select-premium @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo (Habilitado para uso y reportes)</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo (Baja técnica / En mantenimiento)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.equipos.index') }}" class="btn btn-light rounded-3 px-4 fw-bold">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">
                            <i class="bi bi-save me-2"></i> Registrar Equipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push("styles")
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        background-color: var(--bs-body-bg) !important;
        border: 1px solid var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
        border-radius: 10px !important;
        padding: 0.5rem 0.75rem !important;
        height: auto !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        color: var(--bs-body-color) !important;
    }
    .select2-dropdown {
        background-color: var(--bs-body-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
    }
</style>
@endpush

@push("scripts")
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 en los dropdowns
        $('#id_usuario_asignado, #id_marca').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Seleccionar --',
            allowClear: true
        });

        // Carga dinámica de marcas según el tipo de equipo
        $('#id_tipo_equipo').on('change', function() {
            let tipoId = $(this).val();
            let marcaSelect = $('#id_marca');
            let modeloSelect = $('#id_modelo');
            
            marcaSelect.empty().append('<option value="">-- Cargando marcas... --</option>').trigger('change.select2');
            modeloSelect.empty().append('<option value="">-- Primero selecciona una marca --</option>');
            
            if (tipoId) {
                $.ajax({
                    url: `/admin/marcas/por-tipo/${tipoId}`,
                    type: 'GET',
                    success: function(data) {
                        marcaSelect.empty().append('<option value="">-- Seleccionar Marca --</option>');
                        data.forEach(function(marca) {
                            marcaSelect.append(`<option value="${marca.id_marca}">${marca.nombre_marca}</option>`);
                        });
                        marcaSelect.trigger('change.select2');
                    },
                    error: function(xhr) {
                        console.error('Error cargando marcas:', xhr);
                        marcaSelect.empty().append('<option value="">-- Error al cargar marcas --</option>').trigger('change.select2');
                    }
                });
            } else {
                marcaSelect.empty().append('<option value="">-- Primero selecciona tipo de dispositivo --</option>').trigger('change.select2');
            }
        });

        // Carga dinámica de modelos según la marca
        $('#id_marca').on('change', function() {
            let marcaId = $(this).val();
            let modeloSelect = $('#id_modelo');
            modeloSelect.empty().append('<option value="">-- Cargando modelos... --</option>');
            
            if (marcaId) {
                $.ajax({
                    url: `/admin/marcas/${marcaId}/modelos`,
                    type: 'GET',
                    success: function(data) {
                        modeloSelect.empty().append('<option value="">-- Seleccionar Modelo --</option>');
                        data.forEach(function(modelo) {
                            modeloSelect.append(`<option value="${modelo.id_modelo}">${modelo.nombre_modelo}</option>`);
                        });
                    },
                    error: function(xhr) {
                        console.error('Error cargando modelos:', xhr);
                        modeloSelect.empty().append('<option value="">-- Error al cargar modelos --</option>');
                    }
                });
            } else {
                modeloSelect.empty().append('<option value="">-- Primero selecciona una marca --</option>');
            }
        });

        // Mostrar / Ocultar especificaciones dinámicamente según el tipo de equipo
        function toggleTechnicalSection() {
            let selectedType = $('#id_tipo_equipo option:selected').text().trim().toLowerCase();
            let techSection = $('#seccion-tecnica');
            if (selectedType.includes('laptop') || selectedType.includes('desktop') || selectedType.includes('servidor')) {
                techSection.slideDown(300);
            } else {
                techSection.slideUp(300);
            }
        }

        // Ejecutar al cargar y en cambios
        toggleTechnicalSection();
        $('#id_tipo_equipo').on('change', function() {
            toggleTechnicalSection();
        });
    });
</script>
@endpush
