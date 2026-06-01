@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .audit-action-badge {
        font-size: 0.75rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
    }
    .audit-create { background: rgba(25, 135, 84, 0.2); color: #0f5132; border-color: rgba(25, 135, 84, 0.35); }
    .audit-update { background: rgba(255, 193, 7, 0.22); color: #664d03; border-color: rgba(255, 193, 7, 0.38); }
    .audit-delete { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-login { background: rgba(13, 202, 240, 0.2); color: #055160; border-color: rgba(13, 202, 240, 0.35); }
    .audit-logout { background: rgba(108, 117, 125, 0.22); color: #41464b; border-color: rgba(108, 117, 125, 0.35); }
    .audit-failed { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-sync { background: rgba(13, 110, 253, 0.2); color: #084298; border-color: rgba(13, 110, 253, 0.35); }
    .audit-generic { background: rgba(108, 117, 125, 0.18); color: #495057; border-color: rgba(108, 117, 125, 0.3); }

    [data-bs-theme="dark"] .audit-create { color: #75d7ae; background: rgba(25, 135, 84, 0.22); border-color: rgba(25, 135, 84, 0.45); }
    [data-bs-theme="dark"] .audit-update { color: #ffda6a; background: rgba(255, 193, 7, 0.22); border-color: rgba(255, 193, 7, 0.45); }
    [data-bs-theme="dark"] .audit-delete { color: #f5a3ad; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-login { color: #6edff6; background: rgba(13, 202, 240, 0.2); border-color: rgba(13, 202, 240, 0.45); }
    [data-bs-theme="dark"] .audit-logout { color: #ced4da; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }
    [data-bs-theme="dark"] .audit-failed { color: #f1aeb5; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-sync { color: #9ec5fe; background: rgba(13, 110, 253, 0.22); border-color: rgba(13, 110, 253, 0.45); }
    [data-bs-theme="dark"] .audit-generic { color: #dee2e6; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }

    .audit-json {
        font-size: 0.7rem;
        max-width: 250px;
        overflow-x: auto;
        white-space: pre-wrap;
    }
    [data-bs-theme="dark"] .audit-json {
        background: #1f2327 !important;
        color: #e9ecef !important;
        border-color: #343a40 !important;
    }
</style>
@endpush
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
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Crear (created)</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Actualizar (updated)</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Eliminar (deleted)</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Inicio de Sesión</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Cierre de Sesión</option>
                        <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Sesión Fallida</option>
                        <option value="registro_solicitado" {{ request('action') == 'registro_solicitado' ? 'selected' : '' }}>Registro Solicitado</option>
                        <option value="registro_aprobado" {{ request('action') == 'registro_aprobado' ? 'selected' : '' }}>Registro Aprobado</option>
                        <option value="registro_rechazado" {{ request('action') == 'registro_rechazado' ? 'selected' : '' }}>Registro Rechazado</option>
                        <option value="sync_permissions" {{ request('action') == 'sync_permissions' ? 'selected' : '' }}>Sincronizar Permisos</option>
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
                        <option value="User" {{ request('type') == 'User' ? 'selected' : '' }}>Usuarios / Sesiones</option>
                        <option value="Role" {{ request('type') == 'Role' ? 'selected' : '' }}>Roles de Seguridad</option>
                        <option value="Ticket" {{ request('type') == 'Ticket' ? 'selected' : '' }}>Tickets de Soporte</option>
                        <option value="Equipo" {{ request('type') == 'Equipo' ? 'selected' : '' }}>Equipos de Inventario</option>
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
                                <span class="fw-semibold theme-text">{{ $log->created_at->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td class="py-3">
                                @if($log->causer)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            {{ substr($log->causer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold theme-text">{{ $log->causer->name }}</span><br>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.65rem; padding: 2px 6px;">
                                                <i class="bi bi-person-badge-fill me-1"></i>{{ $log->causer->roles->pluck('name')->first() ?? 'Soporte' }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1.5 rounded" style="font-size: 0.75rem;">
                                        <i class="bi bi-cpu-fill me-1 text-secondary"></i>Sistema (Consola / Seeder)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($log->event === 'created' || $log->event === 'create')
                                    <span class="audit-action-badge audit-create">
                                        <i class="bi bi-plus-circle-fill me-1"></i>Creación
                                    </span>
                                @elseif($log->event === 'updated' || $log->event === 'update')
                                    <span class="audit-action-badge audit-update">
                                        <i class="bi bi-pencil-square me-1"></i>Modificación
                                    </span>
                                @elseif($log->event === 'deleted' || $log->event === 'delete')
                                    <span class="audit-action-badge audit-delete">
                                        <i class="bi bi-trash3-fill me-1"></i>Eliminación
                                    </span>
                                @elseif($log->event === 'login')
                                    <span class="audit-action-badge audit-login">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Ingreso
                                    </span>
                                @elseif($log->event === 'logout')
                                    <span class="audit-action-badge audit-logout">
                                        <i class="bi bi-box-arrow-right me-1"></i>Salida
                                    </span>
                                @elseif($log->event === 'login_failed')
                                    <span class="audit-action-badge audit-failed">
                                        <i class="bi bi-exclamation-octagon-fill me-1"></i>Acceso Fallido
                                    </span>
                                @elseif($log->event === 'sync_permissions')
                                    <span class="audit-action-badge audit-sync">
                                        <i class="bi bi-shield-check me-1"></i>Permisos Sinc.
                                    </span>
                                @elseif($log->event === 'registro_solicitado')
                                    <span class="audit-action-badge audit-create" style="background-color: var(--bs-info-bg-subtle); color: var(--bs-info-text-emphasis); border-color: var(--bs-info-border-subtle);">
                                        <i class="bi bi-person-lines-fill me-1"></i>Reg. Solicitado
                                    </span>
                                @elseif($log->event === 'registro_aprobado')
                                    <span class="audit-action-badge audit-create" style="background-color: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); border-color: var(--bs-success-border-subtle);">
                                        <i class="bi bi-person-check-fill me-1"></i>Reg. Aprobado
                                    </span>
                                @elseif($log->event === 'registro_rechazado')
                                    <span class="audit-action-badge audit-delete">
                                        <i class="bi bi-person-x-fill me-1"></i>Reg. Rechazado
                                    </span>
                                @else
                                    <span class="audit-action-badge audit-generic">
                                        {{ strtoupper($log->event) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="fw-bold theme-text">{{ $log->subject_type ? class_basename($log->subject_type) : $log->log_name }}</span>
                            </td>
                            <td class="py-3">
                                <code class="text-secondary fw-bold">#{{ $log->subject_id ?? 'N/A' }}</code>
                            </td>
                            <td class="py-3">
                                @if(isset($log->properties['old']))
                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#old-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="old-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json"><code>{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Registro Nuevo)</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if(isset($log->properties['attributes']) || count($log->properties) > 0)
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#new-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="new-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json"><code>{{ json_encode($log->properties['attributes'] ?? $log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Eliminación)</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4">
                                <small class="fw-semibold theme-text d-block"><i class="bi bi-pc-display me-1 text-muted"></i>{{ $log->properties['ip'] ?? 'Local' }}</small>
                                <small class="text-muted text-truncate d-block" style="max-width: 150px;" title="{{ $log->properties['user_agent'] ?? 'N/A' }}">{{ $log->properties['user_agent'] ?? 'N/A' }}</small>
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
