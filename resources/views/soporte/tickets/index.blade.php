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
            <livewire:soporte.tickets-table />
        </div>
    @endif

    {{-- --- BLOQUE B: VISTA DE TÉCNICO ESPECIALISTA --- --}}
    @if(request()->routeIs('soporte.tickets.tecnico.index'))
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold theme-text mb-1"><i class="bi bi-layout-text-window-reverse me-2 text-primary"></i>Tablero de Trabajo Técnico</h2>
                <p class="text-muted mb-0">Arrastra tus tickets entre columnas para actualizar su progreso operativo.</p>
            </div>
        </div>

        <div class="card-premium shadow-sm border-0 p-4 mb-5">
            <div class="pills-container mb-4 shadow-sm py-2 px-3 rounded-3" style="background: var(--bg-main);">
                <ul class="nav nav-pills nav-fill gap-2" id="tecnico-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold rounded-3" id="pills-kanban-tab" data-bs-toggle="pill" data-bs-target="#pills-kanban" type="button" role="tab">
                            <i class="bi bi-columns-gap me-2"></i> Flujo Técnico
                            <span class="badge bg-primary text-white ms-2">{{ $ticketsPendientes->count() + $ticketsEnProgreso->count() }}</span>
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
                <div class="tab-pane fade show active" id="pills-kanban" role="tabpanel">
                    <div class="row g-3 kanban-board" data-kanban-update-url-template="{{ route('soporte.tickets.tecnico.actualizar-kanban-estado', ['id' => '__TICKET_ID__']) }}">
                        @php
                            $kanbanColumns = [
                                ['key' => 'pendiente', 'title' => 'Pendiente', 'icon' => 'bi-pause-circle', 'badge' => 'warning', 'items' => $ticketsPendientes],
                                ['key' => 'en_progreso', 'title' => 'En progreso', 'icon' => 'bi-play-circle', 'badge' => 'info', 'items' => $ticketsEnProgreso],
                            ];
                        @endphp

                        @foreach($kanbanColumns as $column)
                            <div class="col-12 col-lg-6">
                                <div class="border rounded-3 h-100 d-flex flex-column bg-light-subtle">
                                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="bi {{ $column['icon'] }} me-2"></i>{{ $column['title'] }}
                                        </h6>
                                        <span class="badge bg-{{ $column['badge'] }} text-white rounded-pill">{{ $column['items']->count() }}</span>
                                    </div>

                                    <div class="p-3 flex-grow-1 kanban-column" data-column="{{ $column['key'] }}" style="min-height: 380px;">
                                        @forelse($column['items'] as $ticket)
                                            <div class="card border-0 shadow-sm mb-3 kanban-ticket"
                                                draggable="true"
                                                data-ticket-id="{{ $ticket->id_ticket }}"
                                                data-current-column="{{ $column['key'] }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="fw-bold text-primary">#{{ $ticket->id_ticket }}</span>
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                            {{ $ticket->prioridad->nombre_prioridad ?? 'Sin prioridad' }}
                                                        </span>
                                                    </div>
                                                    <h6 class="mb-1">{{ Str::limit($ticket->asunto, 55) }}</h6>
                                                    <p class="text-muted small mb-2">
                                                        <i class="bi bi-person me-1"></i>{{ $ticket->usuario->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-muted small mb-3">
                                                        <i class="bi bi-tag-fill me-1"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}
                                                    </p>

                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('soporte.tickets.tecnico.show', $ticket->id_ticket) }}"
                                                            class="btn btn-sm btn-outline-info flex-fill btn-action-premium">
                                                            <i class="bi bi-eye-fill me-1"></i> Ver
                                                        </a>

                                                        <a href="{{ route('soporte.tickets.tecnico.resolver', $ticket->id_ticket) }}"
                                                            class="btn btn-sm btn-success flex-fill btn-action-premium">
                                                            <i class="bi bi-check2-circle me-1"></i> Resolver
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted small py-4 border border-dashed rounded-3">
                                                Sin tickets en esta columna.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

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
                                                       class="btn btn-sm btn-outline-warning border-warning-subtle px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold btn-action-premium" style="height: 32px;">
                                                        <i class="bi bi-pencil-square"></i> Editar Solución
                                                    </a>
                                                    <a href="{{ route('soporte.tickets.tecnico.show', $ticket->id_ticket) }}" 
                                                       class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center btn-action-icon" style="width: 32px; height: 32px;" title="Detalles">
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

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const board = document.querySelector('.kanban-board');
                if (!board) {
                    return;
                }

                const csrfToken = '{{ csrf_token() }}';
                const routeTemplate = board.dataset.kanbanUpdateUrlTemplate;
                let draggedCard = null;

                const updateKanbanState = async (ticketId, targetColumn) => {
                    const url = routeTemplate.replace('__TICKET_ID__', ticketId);
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ estado_tecnico: targetColumn }),
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo actualizar el ticket.');
                    }
                };

                board.querySelectorAll('.kanban-ticket[draggable="true"]').forEach((card) => {
                    card.addEventListener('dragstart', () => {
                        draggedCard = card;
                        card.classList.add('opacity-50');
                    });

                    card.addEventListener('dragend', () => {
                        card.classList.remove('opacity-50');
                    });
                });

                board.querySelectorAll('.kanban-column').forEach((column) => {
                    column.addEventListener('dragover', (event) => {
                        event.preventDefault();
                    });

                    column.addEventListener('drop', async (event) => {
                        event.preventDefault();
                        if (!draggedCard) {
                            return;
                        }

                        const fromColumn = draggedCard.dataset.currentColumn;
                        const toColumn = column.dataset.column;
                        if (fromColumn === toColumn) {
                            return;
                        }

                        try {
                            await updateKanbanState(draggedCard.dataset.ticketId, toColumn);
                            draggedCard.dataset.currentColumn = toColumn;
                            column.prepend(draggedCard);
                        } catch (error) {
                            console.error(error);
                            alert('No se pudo mover el ticket. Intenta nuevamente.');
                        } finally {
                            draggedCard = null;
                        }
                    });
                });
            });
        </script>
    @endif
</div>
@endsection
