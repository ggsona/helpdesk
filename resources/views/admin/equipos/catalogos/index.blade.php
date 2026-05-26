@extends("layouts.admin")

@section("content")
    <div class="py-3 px-1">
        
        <!-- Cabecera Premium -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 theme-text">
                    <i class="bi bi-list-stars text-primary me-2"></i> Catálogos de Equipos e Inventario
                </h2>
                <p class="text-secondary mb-0">Administra los tipos de activos, fabricantes y modelos homologados del sistema.</p>
            </div>
        </div>

        {{-- Contenedor para mensajes de notificaciones e integridad --}}
        <div id="ajax-messages" class="mb-4">
            @if (session("success"))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session("success") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session("error"))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger bg-opacity-10 text-danger fw-bold" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session("error") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Pestañas de Navegación (Tabs) -->
        <ul class="nav nav-tabs border-bottom border-secondary border-opacity-10 mb-4 gap-2" id="catalogosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2 border-0 rounded-t-3 {{ request('tab', 'tipos') === 'tipos' ? 'active text-primary' : 'text-secondary' }}" id="tipos-tab" data-bs-toggle="tab" data-bs-target="#tipos-pane" type="button" role="tab" aria-controls="tipos-pane" aria-selected="true">
                    <i class="bi bi-pc-display-horizontal me-2"></i>Tipos de Equipos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2 border-0 rounded-t-3 {{ request('tab') === 'marcas' ? 'active text-primary' : 'text-secondary' }}" id="marcas-tab" data-bs-toggle="tab" data-bs-target="#marcas-pane" type="button" role="tab" aria-controls="marcas-pane" aria-selected="false">
                    <i class="bi bi-award-fill me-2"></i>Fabricantes / Marcas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2 border-0 rounded-t-3 {{ request('tab') === 'modelos' ? 'active text-primary' : 'text-secondary' }}" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos-pane" type="button" role="tab" aria-controls="modelos-pane" aria-selected="false">
                    <i class="bi bi-cpu-fill me-2"></i>Modelos de Activos
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="catalogosTabsContent">
            
            <!-- PESTAÑA TIPOS DE EQUIPOS -->
            <div class="tab-pane fade {{ request('tab', 'tipos') === 'tipos' ? 'show active' : '' }}" id="tipos-pane" role="tabpanel" aria-labelledby="tipos-tab">
                <div class="row">
                    <!-- Formulario de creación rápida -->
                    <div class="col-md-4 mb-4">
                        <div class="card card-premium shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 theme-text"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Registrar Tipo</h5>
                                <form action="{{ route('admin.equipos.catalogos.tipos.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="nombre_tipo_equipo" class="form-label fw-semibold text-secondary small">Nombre del Tipo de Dispositivo</label>
                                        <input type="text" name="nombre_tipo_equipo" id="nombre_tipo_equipo" class="form-control form-control-premium border-secondary border-opacity-25" placeholder="Ej. Laptop, Servidor, UPS..." required>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold shadow-sm">
                                        <i class="bi bi-save me-2"></i>Guardar Tipo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Tipos -->
                    <div class="col-md-8 mb-4">
                        <div class="card card-premium shadow-sm border-0 p-4 mb-4">
                            <form id="search-tipo-form" action="{{ route("admin.equipos.catalogos.index") }}" method="GET" class="mb-3">
                                <input type="hidden" name="tab" value="tipos">
                                <div class="row g-2">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25"><i class="bi bi-search"></i></span>
                                            <input type="text" name="search_tipo" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Buscar tipo..." value="{{ request("search_tipo") }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-info text-white rounded-3 w-100 fw-bold" style="height: calc(2.25rem + 2px);">Filtrar</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead style="background: var(--bg-main);">
                                        <tr class="text-nowrap">
                                            <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="width: 15%;">ID</th>
                                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 70%;">Nombre del Tipo</th>
                                            <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase" style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tipos as $tipo)
                                            <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                                                <td class="ps-4 py-3 fw-semibold text-secondary">#{{ $tipo->id_tipo_equipo }}</td>
                                                <td class="py-3 fw-bold theme-text">{{ $tipo->nombre_tipo_equipo }}</td>
                                                <td class="py-3 text-end pe-4">
                                                    <form action="{{ route('admin.equipos.catalogos.tipos.destroy', $tipo->id_tipo_equipo) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este tipo de equipo? No podrá eliminarse si tiene marcas o activos asociados.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted">
                                                    <i class="bi bi-pc-display-horizontal display-6 mb-2 d-block opacity-25"></i>
                                                    No hay tipos de equipos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Enlaces de paginación para tipos --}}
                        <div class="d-flex justify-content-center">
                            {{ $tipos->appends(['tab' => 'tipos', 'search_tipo' => request('search_tipo')])->links("pagination::bootstrap-5") }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA MARCAS -->
            <div class="tab-pane fade {{ request('tab') === 'marcas' ? 'show active' : '' }}" id="marcas-pane" role="tabpanel" aria-labelledby="marcas-tab">
                <div class="row">
                    <!-- Formulario de creación rápida -->
                    <div class="col-md-4 mb-4">
                        <div class="card card-premium shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 theme-text"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Registrar Marca</h5>
                                <form action="{{ route('admin.marcas.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="nombre_marca" class="form-label fw-semibold text-secondary small">Nombre del Fabricante / Marca</label>
                                        <input type="text" name="nombre_marca" id="nombre_marca" class="form-control form-control-premium border-secondary border-opacity-25" placeholder="Ej. Dell, NVIDIA, Apple..." required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="id_tipo_equipo_marca" class="form-label fw-semibold text-secondary small">Asociar a Tipo de Equipo</label>
                                        <select name="id_tipo_equipo" id="id_tipo_equipo_marca" class="form-select form-select-premium border-secondary border-opacity-25">
                                            <option value="">-- Sin Categoría --</option>
                                            @foreach($todosTipos as $t)
                                                <option value="{{ $t->id_tipo_equipo }}">{{ $t->nombre_tipo_equipo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold shadow-sm">
                                        <i class="bi bi-save me-2"></i>Guardar Marca
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Marcas -->
                    <div class="col-md-8 mb-4">
                        <div class="card card-premium shadow-sm border-0 p-4 mb-4">
                            <form id="search-marca-form" action="{{ route("admin.equipos.catalogos.index") }}" method="GET" class="mb-3">
                                <input type="hidden" name="tab" value="marcas">
                                <div class="row g-2">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25"><i class="bi bi-search"></i></span>
                                            <input type="text" name="search_marca" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Buscar marca..." value="{{ request("search_marca") }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-info text-white rounded-3 w-100 fw-bold" style="height: calc(2.25rem + 2px);">Filtrar</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead style="background: var(--bg-main);">
                                        <tr class="text-nowrap">
                                            <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="width: 10%;">ID</th>
                                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 35%;">Nombre de la Marca</th>
                                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 40%;">Tipo de Equipo Asociado</th>
                                            <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase" style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($marcas as $marca)
                                            <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                                                <td class="ps-4 py-3 fw-semibold text-secondary">#{{ $marca->id_marca }}</td>
                                                <td class="py-3 fw-bold theme-text">{{ $marca->nombre_marca }}</td>
                                                <td class="py-3">
                                                    @if($marca->tipoEquipo)
                                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                                            {{ $marca->tipoEquipo->nombre_tipo_equipo }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">Sin Categoría</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-end pe-4">
                                                    <form action="{{ route('admin.marcas.destroy', $marca->id_marca) }}" method="POST" onsubmit="return confirm('¿Eliminar esta marca? Los modelos vinculados y activos en inventario podrían impedir su eliminación.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="bi bi-award display-6 mb-2 d-block opacity-25"></i>
                                                    No hay marcas registradas.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Enlaces de paginación para marcas --}}
                        <div class="d-flex justify-content-center">
                            {{ $marcas->appends(['tab' => 'marcas', 'search_marca' => request('search_marca')])->links("pagination::bootstrap-5") }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA MODELOS -->
            <div class="tab-pane fade {{ request('tab') === 'modelos' ? 'show active' : '' }}" id="modelos-pane" role="tabpanel" aria-labelledby="modelos-tab">
                <div class="row">
                    <!-- Formulario de creación rápida -->
                    <div class="col-md-4 mb-4">
                        <div class="card card-premium shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 theme-text"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Registrar Modelo</h5>
                                <form action="{{ route('admin.modelos.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="id_marca_select" class="form-label fw-semibold text-secondary small">Fabricante / Marca</label>
                                        <select name="id_marca" id="id_marca_select" class="form-select form-select-premium border-secondary border-opacity-25" required>
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($todasMarcas as $mc)
                                                <option value="{{ $mc->id_marca }}">{{ $mc->nombre_marca }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="nombre_modelo" class="form-label fw-semibold text-secondary small">Nombre del Modelo</label>
                                        <input type="text" name="nombre_modelo" id="nombre_modelo" class="form-control form-control-premium border-secondary border-opacity-25" placeholder="Ej. ThinkPad T14, RTX 3050..." required>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold shadow-sm">
                                        <i class="bi bi-save me-2"></i>Guardar Modelo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Modelos -->
                    <div class="col-md-8 mb-4">
                        <div class="card card-premium shadow-sm border-0 p-4 mb-4">
                            <form id="search-modelo-form" action="{{ route("admin.equipos.catalogos.index") }}" method="GET" class="mb-3">
                                <input type="hidden" name="tab" value="modelos">
                                <div class="row g-2">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25"><i class="bi bi-search"></i></span>
                                            <input type="text" name="search_modelo" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Buscar modelo..." value="{{ request("search_modelo") }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-info text-white rounded-3 w-100 fw-bold" style="height: calc(2.25rem + 2px);">Filtrar</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead style="background: var(--bg-main);">
                                        <tr class="text-nowrap">
                                            <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="width: 15%;">ID</th>
                                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 40%;">Modelo</th>
                                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 30%;">Marca / Fabricante</th>
                                            <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase" style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($modelos as $mod)
                                            <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                                                <td class="ps-4 py-3 fw-semibold text-secondary">#{{ $mod->id_modelo }}</td>
                                                <td class="py-3 fw-bold theme-text">{{ $mod->nombre_modelo }}</td>
                                                <td class="py-3">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                                        {{ $mod->marca->nombre_marca }}
                                                    </span>
                                                </td>
                                                <td class="py-3 text-end pe-4">
                                                    <form action="{{ route('admin.modelos.destroy', $mod->id_modelo) }}" method="POST" onsubmit="return confirm('¿Eliminar este modelo? No podrá eliminarse si hay activos asignados con este modelo.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="bi bi-cpu display-6 mb-2 d-block opacity-25"></i>
                                                    No hay modelos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        {{-- Enlaces de paginación para modelos --}}
                        <div class="d-flex justify-content-center">
                            {{ $modelos->appends(['tab' => 'modelos', 'search_modelo' => request('search_modelo')])->links("pagination::bootstrap-5") }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push("scripts")
<script>
    $(document).ready(function() {
        // Asegurar que al cargar una pestaña mediante URL se mantenga la vista correcta
        const urlParams = new URLSearchParams(window.location.search);
        const activeTabParam = urlParams.get('tab');
        if (activeTabParam) {
            let tabEl = document.querySelector(`#${activeTabParam}-tab`);
            if (tabEl) {
                let tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        }
    });
</script>
@endpush
