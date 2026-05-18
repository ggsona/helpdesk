@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    {{-- Notificaciones del Sistema --}}
    @if(session('success'))
        <div class="alert alert-success border-success-subtle bg-success-subtle text-success-emphasis alert-dismissible fade show rounded-3 p-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger-emphasis alert-dismissible fade show rounded-3 p-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- --- BLOQUE A: VISTA DE GESTOR / COORDINADOR --- --}}
    @if(request()->routeIs('soporte.tickets.index'))
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold theme-text mb-1"><i class="bi bi-ticket-perforated-fill me-2 text-primary"></i>Mesa de Despacho</h2>
                <p class="text-muted mb-0">Asigna especialistas, monitorea el avance y revisa los casos finalizados.</p>
            </div>
        </div>

        <div class="card-premium shadow-sm border-0 p-4 mb-5">
            {{-- Navegación por Pestañas --}}
            <div class="pills-container mb-4 shadow-sm py-2 px-3 rounded-3" style="background: var(--bg-main);">
                <ul class="nav nav-pills nav-fill gap-2" id="gestor-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold rounded-3" id="pills-nuevos-tab" data-bs-toggle="pill" data-bs-target="#pills-nuevos" type="button" role="tab">
                            <i class="bi bi-plus-circle me-2"></i> Por Asignar
                            <span class="badge bg-danger text-white ms-2">{{ $ticketsNuevos->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold rounded-3" id="pills-proceso-tab" data-bs-toggle="pill" data-bs-target="#pills-proceso" type="button" role="tab">
                            <i class="bi bi-clock-history me-2"></i> En Gestión
                            <span class="badge bg-warning text-dark ms-2">{{ $ticketsAsignados->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold rounded-3" id="pills-resueltos-tab" data-bs-toggle="pill" data-bs-target="#pills-resueltos" type="button" role="tab">
                            <i class="bi bi-check-all me-2"></i> Resueltos
                            <span class="badge bg-success text-white ms-2">{{ $ticketsResueltos->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="gestor-tabContent">
                {{-- TAB: POR ASIGNAR --}}
                <div class="tab-pane fade show active" id="pills-nuevos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                            <thead>
                                <tr class="text-custom-muted">
                                    <th style="width: 10%;">ID</th>
                                    <th style="width: 25%;">Cliente</th>
                                    <th style="width: 35%;">Asunto</th>
                                    <th style="width: 15%;">Fecha Reporte</th>
                                    <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketsNuevos as $ticket)
                                    <tr class="border-bottom border-secondary border-opacity-10">
                                        <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                        <td class="fw-bold theme-text">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ Str::limit($ticket->asunto, 40) }}</td>
                                        <td class="small text-muted">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                                <button class="btn btn-sm btn-primary px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                                                    <i class="bi bi-person-plus-fill"></i> Asignar
                                                </button>
                                                <a href="{{ route('soporte.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Ver Detalles">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('soporte.tickets.modals.asignar')
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="opacity-25 mb-3"><i class="bi bi-emoji-smile fs-1 theme-text"></i></div>
                                            <p class="text-muted fst-italic mb-0">¡Excelente! No hay tickets pendientes de asignación por ahora.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: EN GESTIÓN --}}
                <div class="tab-pane fade" id="pills-proceso" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                            <thead>
                                <tr class="text-custom-muted">
                                    <th style="width: 10%;">ID</th>
                                    <th style="width: 25%;">Técnico Especialista</th>
                                    <th style="width: 35%;">Asunto</th>
                                    <th style="width: 15%;">Prioridad</th>
                                    <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketsAsignados as $ticket)
                                    <tr class="border-bottom border-secondary border-opacity-10">
                                        <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                <i class="bi bi-person-circle me-1"></i> {{ $ticket->asignacion->tecnico->name ?? 'Sin Asignar' }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ Str::limit($ticket->asunto, 40) }}</td>
                                        <td>
                                            @php
                                                $prioridad = $ticket->prioridad->nombre_prioridad ?? 'N/A';
                                                $badgeColor = match($prioridad) {
                                                    'Crítica', 'Critica' => 'bg-danger text-white',
                                                    'Alta' => 'bg-warning text-dark',
                                                    'Media' => 'bg-primary text-white',
                                                    'Baja' => 'bg-success text-white',
                                                    default => 'bg-secondary text-white'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeColor }} px-3 py-2 border-0 rounded-pill shadow-sm" style="font-size: 0.75rem; font-weight: 600;">
                                                {{ $prioridad }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                                <button class="btn btn-sm btn-warning text-dark px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                                    <i class="bi bi-arrow-repeat"></i> Reasignar
                                                </button>
                                                <a href="{{ route('soporte.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Ver Detalles">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('soporte.tickets.modals.reasignar')
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="opacity-25 mb-3"><i class="bi bi-gear-wide-connected fs-1 theme-text"></i></div>
                                            <p class="text-muted fst-italic mb-0">No hay tickets activos en gestión técnica por ahora.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: RESUELTOS --}}
                <div class="tab-pane fade" id="pills-resueltos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                            <thead>
                                <tr class="text-custom-muted">
                                    <th style="width: 10%;">ID</th>
                                    <th style="width: 20%;">Cliente</th>
                                    <th style="width: 30%;">Asunto</th>
                                    <th style="width: 20%;">Técnico Resolutor</th>
                                    <th style="width: 10%;">Cierre</th>
                                    <th class="text-end pe-4" style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketsResueltos as $ticket)
                                    <tr class="border-bottom border-secondary border-opacity-10">
                                        <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                        <td class="fw-bold theme-text">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ Str::limit($ticket->asunto, 35) }}</td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                <i class="bi bi-person-check-fill me-1"></i> {{ $ticket->asignacion->tecnico->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $ticket->updated_at->format('d/m/Y') }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                                <button class="btn btn-sm btn-success px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;" data-bs-toggle="modal" data-bs-target="#verResueltoModal{{ $ticket->id_ticket }}">
                                                    <i class="bi bi-file-earmark-check"></i> Cierre
                                                </button>
                                                <a href="{{ route('soporte.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Ver Detalles">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('soporte.tickets.modals.ver_resuelto')
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="opacity-25 mb-3"><i class="bi bi-check-all fs-1 theme-text"></i></div>
                                            <p class="text-muted fst-italic mb-0">No hay registros de incidentes finalizados.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- --- BLOQUE B: VISTA DE TÉCNICO ESPECIALISTA --- --}}
    @if(request()->routeIs('soporte.tickets.tecnico.index'))
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold theme-text mb-1"><i class="bi bi-tools me-2 text-primary"></i>Mi Bandeja Operativa</h2>
                    <p class="text-muted mb-0">Resuelve las solicitudes asignadas ordenadas por el semáforo de prioridades.</p>
                </div>
            </div>

            <div class="card-premium shadow-sm border-0 p-4 mb-5">
                {{-- Navegación Pestañas Técnico --}}
                <div class="pills-container mb-4 shadow-sm py-2 px-3 rounded-3" style="background: var(--bg-main);">
                    <ul class="nav nav-pills nav-fill gap-2" id="tecnico-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold rounded-3" id="pills-asignados-tab" data-bs-toggle="pill" data-bs-target="#pills-asignados" type="button" role="tab">
                                <i class="bi bi-person-workspace me-2"></i> Mis Tareas Asignadas
                                <span class="badge bg-warning text-black ms-2">
                                    {{ $ticketsCriticos->count() + $ticketsAltos->count() + $ticketsMedios->count() + $ticketsBajos->count() }}
                                </span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold rounded-3" id="pills-resueltos-tecnico-tab" data-bs-toggle="pill" data-bs-target="#pills-resueltos-tecnico" type="button" role="tab">
                                <i class="bi bi-check-all me-2"></i> Historial Cerrados
                                <span class="badge bg-success text-white ms-2">{{ $ticketsResueltos->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="tecnico-tabContent">
                    {{-- TAB TÉCNICO: ASIGNADOS POR PRIORIDAD (SEMÁFORO) --}}
                    <div class="tab-pane fade show active" id="pills-asignados" role="tabpanel">
                        @php $tieneTareas = false; @endphp

                        @foreach([
                            'Crítica' => [$ticketsCriticos, 'danger', '🔥 PRIORIDAD CRÍTICA - ATENCIÓN URGENTE'],
                            'Alta'    => [$ticketsAltos, 'warning', '⚡ PRIORIDAD ALTA - RESOLUCIÓN RÁPIDA'],
                            'Media'   => [$ticketsMedios, 'info', '📋 PRIORIDAD MEDIA - ATENCIÓN REGULAR'],
                            'Baja'    => [$ticketsBajos, 'success', '🍃 PRIORIDAD BAJA - PROGRAMABLE']
                        ] as $nombrePrioridad => $config)
                            
                            @if($config[0]->count() > 0)
                                @php $tieneTareas = true; @endphp
                                <div class="mb-5">
                                    <div class="d-flex align-items-center mb-3 bg-{{ $config[1] }} bg-opacity-10 p-3 rounded-3 border-start border-4 border-{{ $config[1] }}">
                                        <h6 class="text-{{ $config[1] }}-emphasis fw-bold mb-0" style="letter-spacing: 0.5px;">
                                            {{ $config[2] }}
                                        </h6>
                                        <span class="badge bg-{{ $config[1] }} text-white ms-3 rounded-pill">{{ $config[0]->count() }}</span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                                            <thead>
                                                <tr class="text-custom-muted">
                                                    <th style="width: 10%;">ID</th>
                                                    <th style="width: 35%;">Asunto / Reportante</th>
                                                    <th style="width: 25%;">Categoría / Equipo</th>
                                                    <th style="width: 15%;">Fecha</th>
                                                    <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($config[0] as $ticket)
                                                    <tr class="border-bottom border-secondary border-opacity-10">
                                                        <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                        <td>
                                                            <div class="fw-bold theme-text mb-1">{{ $ticket->asunto }}</div>
                                                            <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $ticket->usuario->name ?? 'N/A' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 mb-1" style="font-size: 0.7rem;">
                                                                <i class="bi bi-tag-fill me-1"></i>{{ $ticket->categoria->nombre_categoria ?? 'S/C' }}
                                                            </span>
                                                            <div class="small text-muted fs-7">{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'Genérico' }}</div>
                                                        </td>
                                                        <td class="text-muted small">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                                        <td class="text-end pe-4">
                                                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                                                @if($ticket->estatus == 2)
                                                                    <a href="{{ route('soporte.tickets.tecnico.resolver', $ticket->id_ticket) }}" 
                                                                       class="btn btn-sm btn-success px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;">
                                                                        <i class="bi bi-check2-circle"></i> Resolver
                                                                    </a>
                                                                @endif
                                                                <a href="{{ route('soporte.tickets.tecnico.show', $ticket->id_ticket) }}" 
                                                                   class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Detalles & Chat">
                                                                    <i class="bi bi-chat-left-text-fill"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if(!$tieneTareas)
                            <div class="text-center py-5">
                                <div class="opacity-25 mb-3"><i class="bi bi-emoji-sunglasses-fill fs-1 theme-text"></i></div>
                                <h5 class="fw-bold theme-text mb-1">¡Todo en orden, Especialista!</h5>
                                <p class="text-muted mb-0">No tienes tareas asignadas pendientes en este momento.</p>
                            </div>
                        @endif
                    </div>

                    {{-- TAB TÉCNICO: HISTORIAL DE RESUELTOS --}}
                    <div class="tab-pane fade" id="pills-resueltos-tecnico" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                                <thead>
                                    <tr class="text-custom-muted">
                                        <th style="width: 10%;">ID</th>
                                        <th style="width: 35%;">Asunto / Reportante</th>
                                        <th style="width: 25%;">Solución Técnica</th>
                                        <th style="width: 15%;">Fecha Cierre</th>
                                        <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ticketsResueltos as $ticket)
                                        <tr class="border-bottom border-secondary border-opacity-10">
                                            <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                            <td>
                                                <div class="fw-bold theme-text mb-1">{{ $ticket->asunto }}</div>
                                                <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $ticket->usuario->name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <div class="text-success small fst-italic">
                                                    <i class="bi bi-journal-check me-1"></i> {{ Str::limit($ticket->solucion->resumen_usuario ?? 'Cerrado satisfactoriamente', 40) }}
                                                </div>
                                            </td>
                                            <td class="text-muted small">{{ $ticket->updated_at->format('d/m/Y') }}</td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2 align-items-center">
                                                    <a href="{{ route('soporte.tickets.tecnico.editar-solucion', $ticket->id_ticket) }}" 
                                                       class="btn btn-sm btn-outline-warning border-warning-subtle px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;">
                                                        <i class="bi bi-pencil-square"></i> Editar Solución
                                                    </a>
                                                    <a href="{{ route('soporte.tickets.tecnico.show', $ticket->id_ticket) }}" 
                                                       class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Detalles">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="opacity-25 mb-3"><i class="bi bi-archive-fill fs-1 theme-text"></i></div>
                                                <p class="text-muted fst-italic mb-0">No has registrado soluciones recientemente.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    @endif
</div>
@endsection
