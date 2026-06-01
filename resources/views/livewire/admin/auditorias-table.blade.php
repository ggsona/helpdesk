<div>
    <!-- Filtros y Búsqueda -->
    <div class="card card-premium shadow-sm border-0 mb-2">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small mb-1">Buscar por tipo, acción o responsable</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-secondary" style="height: 42px;"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Ej. Categoria, create, Admin..." style="height: 42px;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Acción Realizada</label>
                    <select wire:model.live="action" class="form-select form-select-premium border-secondary border-opacity-25" style="height: 42px;">
                        <option value="">Todas las acciones</option>
                        <option value="created">Crear (created)</option>
                        <option value="updated">Actualizar (updated)</option>
                        <option value="deleted">Eliminar (deleted)</option>
                        <option value="login">Inicio de Sesión</option>
                        <option value="logout">Cierre de Sesión</option>
                        <option value="login_failed">Sesión Fallida</option>
                        <option value="registro_solicitado">Registro Solicitado</option>
                        <option value="registro_aprobado">Registro Aprobado</option>
                        <option value="registro_rechazado">Registro Rechazado</option>
                        <option value="sync_permissions">Sincronizar Permisos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Componente / Tabla</label>
                    <select wire:model.live="type" class="form-select form-select-premium border-secondary border-opacity-25" style="height: 42px;">
                        <option value="">Todos los componentes</option>
                        <option value="Categoria">Categorías</option>
                        <option value="TipoEquipo">Tipos de Equipos</option>
                        <option value="Marca">Marcas</option>
                        <option value="Modelo">Modelos</option>
                        <option value="User">Usuarios / Sesiones</option>
                        <option value="Role">Roles de Seguridad</option>
                        <option value="Ticket">Tickets de Soporte</option>
                        <option value="Equipo">Equipos de Inventario</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-light rounded-3 px-3 fw-bold w-100" style="height: 42px; display: inline-flex; align-items: center; justify-content: center;" title="Limpiar filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Logs Ampliada (Premium sin padding en la tarjeta) -->
    <div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4 position-relative">
        
        <!-- Indicador de Carga Overlay -->
        <div wire:loading.flex class="position-absolute w-100 h-100 justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 10; top: 0; left: 0; backdrop-filter: blur(2px);">
            <div class="text-primary d-flex flex-column align-items-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <span class="mt-2 fw-semibold">Cargando bitácora...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: var(--bg-main);">
                    <tr class="text-nowrap">
                        <th class="ps-3 py-3 border-0 text-muted small text-uppercase">Fecha y Hora</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Responsable</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Acción</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Elemento Afectado</th>
                        <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="min-width: 160px;">Detalles del Cambio</th>
                        <th class="py-3 border-0 text-muted small text-uppercase pe-3">IP y Navegador</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);" wire:key="log-{{ $log->id }}">
                            <td class="ps-3 py-3">
                                <span class="fw-semibold theme-text">{{ $log->created_at->tz(config('app.timezone'))->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $log->created_at->tz(config('app.timezone'))->format('H:i:s') }}</small>
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
                                        <i class="bi bi-cpu-fill me-1 text-secondary"></i>Sistema (Automático)
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
                                @if($log->subject_id)
                                    <code class="text-secondary ms-1">#{{ $log->subject_id }}</code>
                                @endif
                            </td>
                            <td class="ps-4 py-3">
                                <div class="d-flex flex-column gap-1">
                                    @if(isset($log->properties['old']))
                                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-2 text-start" data-bs-toggle="collapse" data-bs-target="#old-{{ $log->id }}" style="font-size: 0.70rem;">
                                            <i class="bi bi-clock-history me-1"></i> Anterior <i class="bi bi-chevron-down float-end ms-2"></i>
                                        </button>
                                        <div class="collapse" id="old-{{ $log->id }}" wire:ignore.self>
                                            <pre class="bg-light p-1 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json" style="font-size: 0.65rem;"><code>{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        </div>
                                    @endif

                                    @if(isset($log->properties['attributes']) || (count($log->properties) > 0 && !isset($log->properties['old'])))
                                        <button class="btn btn-sm btn-outline-primary py-1 px-2 rounded-2 text-start" data-bs-toggle="collapse" data-bs-target="#new-{{ $log->id }}" style="font-size: 0.70rem;">
                                            <i class="bi bi-lightning-charge me-1"></i> Nuevo <i class="bi bi-chevron-down float-end ms-2"></i>
                                        </button>
                                        <div class="collapse" id="new-{{ $log->id }}" wire:ignore.self>
                                            <pre class="bg-light p-1 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json" style="font-size: 0.65rem;"><code>{{ json_encode($log->properties['attributes'] ?? $log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        </div>
                                    @endif
                                    
                                    @if(!isset($log->properties['old']) && !isset($log->properties['attributes']) && count($log->properties) == 0)
                                        <span class="text-muted small italic">Ninguno</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 pe-3">
                                <small class="fw-semibold theme-text d-block"><i class="bi bi-pc-display me-1 text-muted"></i>{{ $log->properties['ip'] ?? 'Local' }}</small>
                                <small class="text-muted text-truncate d-block" style="max-width: 120px;" title="{{ $log->properties['user_agent'] ?? 'N/A' }}">{{ $log->properties['user_agent'] ?? 'N/A' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
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
            {{ $logs->links() }}
        </div>
    @endif
</div>
