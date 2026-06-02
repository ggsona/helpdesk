@extends("layouts.admin")

@section("content")
    <div class="mb-4">
        <a href="{{ route("admin.roles.index") }}" class="text-decoration-none small text-muted hover-primary">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista de roles
        </a>
        <h2 class="fw-bold theme-text mt-3"><i class="bi bi-shield-plus me-2 text-primary"></i>Crear Nuevo Rol</h2>
        <p class="text-muted">Define un nuevo rol operativo y selecciona los privilegios y permisos que le corresponderán.</p>
    </div>

    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card-premium shadow-sm p-4 mb-5 border-0">
                <form action="{{ route("admin.roles.store") }}" method="POST">
                    @csrf
                    
                    {{-- Nombre del Rol --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold theme-text" for="name">Nombre del Rol</label>
                        <input type="text" name="name" id="name" 
                               class="form-control form-control-premium @error("name") is-invalid @enderror" 
                               placeholder="Ej: tecnico-junior, gestor-redes..." 
                               value="{{ old("name") }}" required>
                        <small class="text-muted d-block mt-1">El nombre se convertirá a minúsculas automáticamente en el sistema.</small>
                        @error("name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4 opacity-25">

                    {{-- Grilla de Permisos agrupados por categoría --}}
                    <h5 class="fw-bold theme-text mb-3"><i class="bi bi-check2-all text-primary me-2"></i>Asignar Permisos del Rol</h5>
                    <p class="text-muted small mb-4">Marca los accesos y acciones operativas autorizadas para los usuarios que tengan este rol:</p>

                    @php
                    $grupos = [
                        'Tickets' => [
                            'icon'  => 'bi-ticket-perforated-fill',
                            'color' => 'primary',
                            'desc'  => 'Gestión del ciclo de vida de los tickets de soporte.',
                            'keys'  => ['crear-tickets','ver-panel-operativo','asignar-tickets','resolver-tickets','comentar-interno','cerrar-tickets','reabrir-tickets'],
                        ],
                        'Usuarios y Roles' => [
                            'icon'  => 'bi-people-fill',
                            'color' => 'success',
                            'desc'  => 'Administración de usuarios, roles y aprobaciones.',
                            'keys'  => ['gestionar-usuarios','gestionar-roles','aprobar-usuarios'],
                        ],
                        'Configuración y Catálogos' => [
                            'icon'  => 'bi-sliders',
                            'color' => 'secondary',
                            'desc'  => 'Configuración general del sistema y catálogos de datos.',
                            'keys'  => ['ver-configuraciones','gestionar-categorias','gestionar-catalogos'],
                        ],
                        'Equipos / Inventario' => [
                            'icon'  => 'bi-pc-display',
                            'color' => 'info',
                            'desc'  => 'Acceso y gestión del inventario de equipos.',
                            'keys'  => ['gestionar-equipos','ver-equipos'],
                        ],
                        'Reportes y Auditoría' => [
                            'icon'  => 'bi-graph-up-arrow',
                            'color' => 'warning',
                            'desc'  => 'Visualización de estadísticas, bitácoras y exportación de datos.',
                            'keys'  => ['ver-rendimiento-tecnico','ver-auditorias','exportar-reportes'],
                        ],
                        'Base de Conocimiento' => [
                            'icon'  => 'bi-journal-bookmark-fill',
                            'color' => 'danger',
                            'desc'  => 'Acceso y gestión de artículos, etiquetas e impresión. <strong class="text-danger">eliminar-articulo</strong> es exclusivo del Admin.',
                            'keys'  => ['ver-conocimiento','crear-articulo','editar-articulo','archivar-articulo','gestionar-tags','imprimir-articulo','eliminar-articulo'],
                        ],
                    ];

                    $permisosIndexados = $permissions->keyBy('name');
                    $todasLasKeys = collect($grupos)->pluck('keys')->flatten()->all();
                    $sinGrupo = $permissions->filter(fn($p) => !in_array($p->name, $todasLasKeys));
                    $oldPerms = is_array(old('permissions')) ? old('permissions') : [];
                    @endphp

                    @foreach($grupos as $nombreGrupo => $grupo)
                        @php
                            $permisosDelGrupo = collect($grupo['keys'])
                                ->map(fn($key) => $permisosIndexados->get($key))
                                ->filter();
                        @endphp

                        @if($permisosDelGrupo->isNotEmpty())
                        <div class="d-flex align-items-center gap-3 mt-4 mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm flex-shrink-0"
                                 style="width:40px;height:40px;background:rgba(var(--bs-{{ $grupo['color'] }}-rgb),.12);">
                                <i class="bi {{ $grupo['icon'] }} text-{{ $grupo['color'] }} fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 theme-text">{{ $nombreGrupo }}</h6>
                                <small class="text-muted">{!! $grupo['desc'] !!}</small>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            @foreach($permisosDelGrupo as $permiso)
                            <div class="col-md-6">
                                <div class="card p-3 shadow-sm border border-{{ $grupo['color'] }} border-opacity-10 h-100 theme-bg-dark" style="border-radius:12px;">
                                    <div class="form-check d-flex align-items-start">
                                        <input class="form-check-input mt-1 me-2 shadow-none"
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permiso->id }}"
                                               id="perm_{{ $permiso->id }}"
                                               {{ in_array($permiso->id, $oldPerms) ? 'checked' : '' }}>
                                        <div class="ms-1">
                                            <label class="form-check-label fw-bold theme-text" for="perm_{{ $permiso->id }}">
                                                <i class="bi {{ $grupo['icon'] }} text-{{ $grupo['color'] }} me-1 small"></i>
                                                {{ str_replace('-', ' ', $permiso->name) }}
                                            </label>
                                            @if($permiso->name === 'eliminar-articulo')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1 small">Solo Admin</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    @endforeach

                    {{-- Permisos sin grupo (futuros) --}}
                    @if($sinGrupo->isNotEmpty())
                    <div class="d-flex align-items-center gap-3 mt-4 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm flex-shrink-0"
                             style="width:40px;height:40px;background:rgba(var(--bs-secondary-rgb),.12);">
                            <i class="bi bi-shield-check text-secondary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 theme-text">Otros Permisos</h6>
                            <small class="text-muted">Permisos adicionales del sistema.</small>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        @foreach($sinGrupo as $permiso)
                        <div class="col-md-6">
                            <div class="card p-3 shadow-sm border h-100 theme-bg-dark" style="border-radius:12px;">
                                <div class="form-check d-flex align-items-start">
                                    <input class="form-check-input mt-1 me-2 shadow-none"
                                           type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permiso->id }}"
                                           id="perm_{{ $permiso->id }}"
                                           {{ in_array($permiso->id, $oldPerms) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold theme-text" for="perm_{{ $permiso->id }}">
                                        <i class="bi bi-shield-check text-secondary me-1 small"></i>
                                        {{ str_replace('-', ' ', $permiso->name) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="text-end mt-5">
                        <hr class="my-4 opacity-25">
                        <a href="{{ route("admin.roles.index") }}" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                            <i class="bi bi-check-circle me-1"></i> Crear Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
