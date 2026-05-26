<div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background: var(--bg-main);">
                <tr class="text-nowrap">
                    <th class="ps-4 py-3 border-0 text-muted small text-uppercase">ID</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Nombre</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Estado</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Creada</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Actualizada</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Creado por</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Modificado por</th>
                    <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categorias as $categoria)
                    <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                        <td class="ps-4 py-3 fw-semibold text-secondary">#{{ $categoria->id_categoria }}</td>
                        <td class="py-3 fw-bold theme-text">{{ $categoria->nombre_categoria }}</td>
                        <td class="py-3">
                            @if ($categoria->estado)
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Activa
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi bi-x-circle-fill me-1"></i> Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-secondary small">{{ $categoria->created_at->format("d/m/Y H:i") }}</td>
                        <td class="py-3 text-secondary small">{{ $categoria->updated_at->format("d/m/Y H:i") }}</td>
                        <td class="py-3 small text-secondary">{{ $categoria->creator->name ?? "N/A" }}</td>
                        <td class="py-3 small text-secondary">{{ $categoria->updater->name ?? "N/A" }}</td>
                        <td class="py-3 text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route("admin.categorias.edit", $categoria->id_categoria) }}" class="btn btn-sm btn-outline-warning border-warning-subtle px-3 rounded-3 d-flex align-items-center shadow-sm">
                                    <i class="bi bi-pencil-square me-2"></i> Editar
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="opacity-25 mb-3">
                                <i class="bi bi-tags display-1 theme-text"></i>
                            </div>
                            <p class="text-muted fst-italic">No hay categorías registradas en el sistema.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Enlaces de paginación --}}
<div class="d-flex justify-content-center mt-4">
    {{ $categorias->links("pagination::bootstrap-5") }}
</div>