@extends("layouts.admin")

@section("content")
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Gestión de Categorías</h1>

        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route("admin.categorias.create") }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nueva Categoría
            </a>
        </div>

        {{-- Contenedor para mensajes AJAX --}}
        <div id="ajax-messages" class="mb-4">
            @if (session("success"))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session("success") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session("error"))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session("error") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="card shadow mb-4 card-premium">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Categorías</h6>
            </div>
            <div class="card-body">
                {{-- Formulario de búsqueda y filtro de estado --}}
                <form id="filter-form" action="{{ route("admin.categorias.index") }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre..." value="{{ request("search") }}">
                        </div>
                        <div class="col-md-3">
                            <select name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request("estado") == "activo" ? "selected" : "" }}>Activas</option>
                                <option value="inactivo" {{ request("estado") == "inactivo" ? "selected" : "" }}>Inactivas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info">Buscar</button>
                        </div>
                        @if (request()->filled("search") || request()->filled("estado"))
                            <div class="col-md-3">
                                <a href="{{ route("admin.categorias.index") }}" class="btn btn-secondary">Limpiar Filtros</a>
                            </div>
                        @endif
                    </div>
                </form>

                <div id="categorias-list">
                    @include("admin.categorias._categorias_table", ["categorias" => $categorias])
                </div>

            </div>
        </div>
    </div>
@endsection

@push("scripts")
<script>
    $(document).ready(function() {
        function showMessage(type, message) {
            let alertClass = \'\';
            if (type === \'success\') {
                alertClass = \'alert-success\';
            } else if (type === \'error\') {
                alertClass = \'alert-danger\';
            }
            $(\'#ajax-messages\').html(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            // Desaparecer el mensaje después de 5 segundos
            setTimeout(function() {
                $(\'#ajax-messages .alert\').alert(\'close\');
            }, 5000);
        }

        function loadCategories(url) {
            $.ajax({
                url: url,
                type: \'GET\',
                data: $(\'#filter-form\').serialize(), // Incluir los parámetros de filtro
                headers: {
                    \'X-Requested-With\': \'XMLHttpRequest\' // Indicar que es una solicitud AJAX
                },
                success: function(response) {
                    $(\'#categorias-list\').html(response);
                    // No es necesario re-bindear eventos de paginación si se delegan desde $(document)
                },
                error: function(xhr) {
                    console.error(\'Error cargando categorías:\', xhr);
                    showMessage(\'error\', \'Hubo un error al cargar las categorías.\');
                }
            });
        }

        // Cargar categorías al enviar el formulario de filtro/búsqueda
        $(\'#filter-form\').on(\'submit\', function(e) {
            e.preventDefault();
            loadCategories($(this).attr(\'action\'));
        });

        // Cargar categorías al hacer clic en un enlace de paginación
        // Delegar el evento porque los enlaces se recargan con AJAX
        $(document).on(\'click\', \'#categorias-list .pagination a\', function(e) {
            e.preventDefault();
            loadCategories($(this).attr(\'href\'));
        });
    });
</script>
@endpush
