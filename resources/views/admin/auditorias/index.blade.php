@extends('layouts.admin')

@section('content')
<div class="py-3 px-1">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h2 fw-bold mb-1 theme-text"><i class="bi bi-journal-text text-primary me-2"></i>Bitácora de Auditorías</h1>
            <p class="text-secondary mb-2">Monitorea y audita detalladamente qué usuario creó, modificó o eliminó elementos en la plataforma.</p>
            <div class="d-flex gap-2 mt-2">
                <a href="{{ route('admin.auditorias.export', request()->query()) }}" class="btn btn-success rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Excel (CSV)
                </a>
                <a href="{{ route('admin.auditorias.pdf', request()->query()) }}" class="btn btn-danger rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card card-premium shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.auditorias.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small mb-1">Buscar por tipo, acción o responsable</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Ej. Categoria, create, Admin..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Acción Realizada</label>
                    <select name="action" class="form-select form-select-premium border-secondary border-opacity-25" onchange="this.form.submit()">
                        <option value="">Todas las acciones</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Crear (create)</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Actualizar (update)</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Eliminar (delete)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Componente / Tabla</label>
                    <select name="type" class="form-select form-select-premium border-secondary border-opacity-25" onchange="this.form.submit()">
                        <option value="">Todos los componentes</option>
                        <option value="Categoria" {{ request('type') == 'Categoria' ? 'selected' : '' }}>Categorías</option>
                        <option value="TipoEquipo" {{ request('type') == 'TipoEquipo' ? 'selected' : '' }}>Tipos de Equipos</option>
                        <option value="Marca" {{ request('type') == 'Marca' ? 'selected' : '' }}>Marcas</option>
                        <option value="Modelo" {{ request('type') == 'Modelo' ? 'selected' : '' }}>Modelos</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('admin.auditorias.index') }}" class="btn btn-light rounded-3 px-3 fw-bold w-100" style="height: calc(2.25rem + 2px); display: inline-flex; align-items: center; justify-content: center;" title="Limpiar filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Logs Ampliada (Premium sin padding en la tarjeta) -->
    <div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: var(--bg-main);">
                    <tr class="text-nowrap">
                        <th class="ps-4 py-3 border-0 text-muted small text-uppercase">Fecha y Hora</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Responsable del Cambio</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Tipo de Acción</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Elemento Afectado</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">ID Ref</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Valores Anteriores</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Valores Nuevos</th>
                        <th class="py-3 border-0 text-muted small text-uppercase pe-4">Dirección IP y Navegador</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                            <td class="ps-4 py-3">
                                <span class="fw-semibold text-dark">{{ $log->created_at->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td class="py-3">
                                @if($log->user)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            {{ substr($log->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold theme-text">{{ $log->user->name }}</span><br>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.65rem; padding: 2px 6px;">
                                                <i class="bi bi-person-badge-fill me-1"></i>{{ $log->user->roles->pluck('name')->first() ?? 'Soporte' }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-dark bg-opacity-10 text-dark-emphasis px-2 py-1.5 rounded" style="font-size: 0.75rem;">
                                        <i class="bi bi-cpu-fill me-1 text-secondary"></i>Sistema (Consola / Seeder)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($log->action === 'create')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-plus-circle-fill me-1"></i>Creación
                                    </span>
                                @elseif($log->action === 'update')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-pencil-square me-1"></i>Modificación
                                    </span>
                                @elseif($log->action === 'delete')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-trash3-fill me-1"></i>Eliminación
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="fw-bold theme-text">{{ class_basename($log->auditable_type) }}</span>
                            </td>
                            <td class="py-3">
                                <code class="text-secondary fw-bold">#{{ $log->auditable_id }}</code>
                            </td>
                            <td class="py-3">
                                @if($log->old_values)
                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#old-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="old-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap" style="font-size: 0.7rem; max-width: 250px; overflow-x: auto; white-space: pre-wrap;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Registro Nuevo)</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($log->new_values)
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#new-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="new-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap" style="font-size: 0.7rem; max-width: 250px; overflow-x: auto; white-space: pre-wrap;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Eliminación)</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4">
                                <small class="fw-semibold text-dark d-block"><i class="bi bi-pc-display me-1 text-muted"></i>{{ $log->ip_address ?? 'Local' }}</small>
                                <small class="text-muted text-truncate d-block" style="max-width: 150px;" title="{{ $log->user_agent }}">{{ $log->user_agent ?? 'N/A' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-info-circle text-muted fs-2 mb-3 d-block"></i>
                                <p class="text-muted mb-0">No se encontraron registros de auditoría en la bitácora.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
