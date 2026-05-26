<div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background: var(--bg-main);">
                <tr class="text-nowrap">
                    <th class="ps-4 py-3 border-0 text-muted small text-uppercase">ID</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">No. Bien Inst.</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Tipo</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Nombre</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Marca/Modelo</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Asignado a</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">IP / MAC</th>
                    <th class="py-3 border-0 text-muted small text-uppercase">Estado</th>
                    <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($equipos as $equipo)
                    <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                        <td class="ps-4 py-3 fw-semibold text-secondary">#{{ $equipo->id_equipo }}</td>
                        <td class="py-3">
                            @if($equipo->numero_bien)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1.5 fw-bold">
                                    <i class="bi bi-barcode me-1"></i>{{ $equipo->numero_bien }}
                                </span>
                            @else
                                <span class="text-muted small">No tiene</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                                {{ $equipo->tipoEquipo->nombre_tipo_equipo }}
                            </span>
                        </td>
                        <td class="py-3 fw-bold theme-text">{{ $equipo->nombre }}</td>
                        <td class="py-3 small text-overflow-wrap">
                            @if($equipo->marca || $equipo->modelo)
                                <span class="fw-semibold">{{ $equipo->marca->nombre_marca ?? 'N/A' }}</span>
                                <div class="text-secondary small">{{ $equipo->modelo->nombre_modelo ?? 'N/A' }}</div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                            @if($equipo->ram || $equipo->procesador || $equipo->disco_duro)
                                <div class="mt-1 small bg-secondary bg-opacity-10 text-secondary rounded p-1.5" style="font-size: 0.7rem; max-width: 140px;">
                                    @if($equipo->procesador) <div class="text-truncate" title="{{ $equipo->procesador }}"><i class="bi bi-cpu me-1"></i>{{ $equipo->procesador }}</div> @endif
                                    @if($equipo->ram) <div><i class="bi bi-memory me-1"></i>RAM: {{ $equipo->ram }}</div> @endif
                                    @if($equipo->disco_duro) <div class="text-truncate" title="{{ $equipo->disco_duro }}"><i class="bi bi-hdd-fill me-1"></i>{{ $equipo->disco_duro }}</div> @endif
                                </div>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($equipo->usuarioAsignado)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                        {{ substr($equipo->usuarioAsignado->name, 0, 1) }}
                                    </div>
                                    <div class="text-truncate" style="max-width: 130px;">
                                        <span class="fw-semibold text-dark theme-text small d-block">{{ $equipo->usuarioAsignado->name }}</span>
                                        <small class="text-secondary" style="font-size: 0.7rem;">{{ $equipo->usuarioAsignado->persona->id_unidad_administrativa ? $equipo->usuarioAsignado->persona->unidadAdministrativa->nombre : 'Sin Área' }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1.5 rounded-pill fw-semibold small">
                                    <i class="bi bi-person-x-fill me-1"></i> Sin Asignar
                                </span>
                            @endif
                        </td>
                        <td class="py-3 small">
                            @if($equipo->ip_address || $equipo->mac_address)
                                <div><code class="text-primary" style="font-size: 0.75rem;"><i class="bi bi-laptop me-1"></i>{{ $equipo->ip_address ?? '—' }}</code></div>
                                <div class="text-secondary" style="font-size: 0.7rem;"><i class="bi bi-fingerprint me-1"></i>{{ $equipo->mac_address ?? '—' }}</div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if ($equipo->estado)
                                <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill fw-semibold small">
                                    Activo
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1.5 rounded-pill fw-semibold small">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.equipos.edit', $equipo->id_equipo) }}" class="btn btn-sm btn-outline-warning border-warning-subtle px-3 rounded-3 d-flex align-items-center shadow-sm">
                                    <i class="bi bi-pencil-square me-2"></i> Editar
                                </a>
                                <form action="{{ route('admin.equipos.destroy', $equipo->id_equipo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este equipo de forma permanente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="opacity-25 mb-3">
                                <i class="bi bi-pc-display display-1 theme-text"></i>
                            </div>
                            <p class="text-muted fst-italic">No se encontraron equipos registrados en el inventario.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Enlaces de paginación --}}
<div class="d-flex justify-content-center mt-4">
    {{ $equipos->links("pagination::bootstrap-5") }}
</div>
