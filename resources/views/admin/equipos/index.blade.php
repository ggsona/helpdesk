@extends("layouts.admin")

@section("content")
    <div class="py-3 px-1">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 theme-text">
                    <i class="bi bi-pc-display text-primary me-2"></i> Asignación y Control de Equipos
                </h2>
                <p class="text-secondary mb-0">Gestiona el inventario de hardware institucional y su asignación a los usuarios.</p>
            </div>
            <a href="{{ route("admin.equipos.create") }}" class="btn btn-primary rounded-3 px-4 shadow-sm d-inline-flex align-items-center gap-2 fw-bold">
                <i class="bi bi-plus-lg"></i> Registrar Nuevo Equipo
            </a>
        </div>

        {{-- Alertas de éxito o error --}}
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

        <div class="card card-premium shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                {{-- Formulario de búsqueda y filtros --}}
                <form id="filter-form" action="{{ route("admin.equipos.index") }}" method="GET">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small mb-1">Buscar Equipo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Nombre, No. Bien, Marca o Modelo..." value="{{ request("search") }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small mb-1">Tipo de Equipo</label>
                            <select name="id_tipo_equipo" class="form-select form-select-premium border-secondary border-opacity-25">
                                <option value="">Todos los tipos</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_equipo }}" {{ request("id_tipo_equipo") == $tipo->id_tipo_equipo ? "selected" : "" }}>
                                        {{ $tipo->nombre_tipo_equipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small mb-1">Estado Físico</label>
                            <select name="estado" class="form-select form-select-premium border-secondary border-opacity-25">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request("estado") == "activo" ? "selected" : "" }}>Activos</option>
                                <option value="inactivo" {{ request("estado") == "inactivo" ? "selected" : "" }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2 mt-4 mt-md-0">
                            <button type="submit" class="btn btn-info text-white rounded-3 px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="height: calc(2.25rem + 2px);">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            @if (request()->filled("search") || request()->filled("id_tipo_equipo") || request()->filled("estado"))
                                <a href="{{ route("admin.equipos.index") }}" class="btn btn-light rounded-3 px-3 fw-bold" style="height: calc(2.25rem + 2px); display: inline-flex; align-items: center;">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="equipos-list">
            @include("admin.equipos._equipos_table", ["equipos" => $equipos])
        </div>
    </div>
@endsection

@push("scripts")
<script>
    $(document).ready(function() {
        function showMessage(type, message) {
            let alertClass = '';
            let icon = '';
            if (type === 'success') {
                alertClass = 'alert-success bg-success bg-opacity-10 text-success';
                icon = '<i class="bi bi-check-circle-fill me-2"></i>';
            } else if (type === 'error') {
                alertClass = 'alert-danger bg-danger bg-opacity-10 text-danger';
                icon = '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
            }
            $('#ajax-messages').html(`
                <div class="alert ${alertClass} alert-dismissible fade show shadow-sm border-0 fw-bold" role="alert">
                    ${icon} ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            setTimeout(function() {
                $('#ajax-messages .alert').alert('close');
            }, 5000);
        }

        function loadEquipos(url) {
            $.ajax({
                url: url,
                type: 'GET',
                data: $('#filter-form').serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    $('#equipos-list').html(response);
                },
                error: function(xhr) {
                    console.error('Error cargando equipos:', xhr);
                    showMessage('error', 'Hubo un error al cargar la lista de equipos.');
                }
            });
        }

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            loadEquipos($(this).attr('action'));
        });

        $(document).on('click', '#equipos-list .pagination a', function(e) {
            e.preventDefault();
            loadEquipos($(this).attr('href'));
        });
    });
</script>
@endpush
