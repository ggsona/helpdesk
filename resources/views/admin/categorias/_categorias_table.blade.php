<div class="table-responsive">
    <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Nombre</th> {{-- Ajuste de ancho --}}
                <th style="width: 10%;">Estado</th>
                <th style="width: 12%;">Creada</th>
                <th style="width: 12%;">Actualizada</th>
                <th style="width: 10%;">Creado por</th> {{-- Nueva columna --}}
                <th style="width: 10%;">Modificado por</th> {{-- Nueva columna --}}
                <th style="width: 21%;">Acciones</th> {{-- Ajuste de ancho --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($categorias as $categoria)
                <tr>
                    <td>{{ $categoria->id_categoria }}</td>
                    <td>{{ $categoria->nombre_categoria }}</td>
                    <td>
                        @if ($categoria->estado)
                            <span class="badge bg-success">Activa</span>
                        @else
                            <span class="badge bg-danger">Inactiva</span>
                        @endif
                    </td>
                    <td>{{ $categoria->created_at->format("d/m/Y H:i") }}</td>
                    <td>{{ $categoria->updated_at->format("d/m/Y H:i") }}</td>
                    <td>{{ $categoria->creator->name ?? "N/A" }}</td> {{-- Mostrar nombre del creador --}}
                    <td>{{ $categoria->updater->name ?? "N/A" }}</td> {{-- Mostrar nombre del último editor --}}
                    <td>
                        <a href="{{ route("admin.categorias.edit", $categoria->id_categoria) }}" class="btn btn-sm btn-warning mb-1 text-nowrap">
                            <i class="bi bi-pencil"></i> Editar
                        </a>


                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Enlaces de paginación --}}
<div class="d-flex justify-content-center">
    {{ $categorias->links("pagination::bootstrap-5") }}
</div>