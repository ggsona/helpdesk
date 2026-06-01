<div>
    {{-- Navegación y Filtros Premium --}}
    <div class="card card-premium shadow-sm border-0 mb-2">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-5 col-md-12">
                    <label class="form-label fw-semibold text-secondary small mb-1">Buscar Ticket</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Ej. Asunto, Cliente o ID...">
                    </div>
                </div>
                <div class="col-xl-8 col-lg-7 col-md-12 text-start text-lg-end">
                    <div class="btn-group shadow-sm text-nowrap d-inline-flex" role="group">
                        <button type="button" class="btn {{ $filtroEstado == '1' ? 'btn-danger fw-bold' : 'btn-outline-secondary border-opacity-25 text-secondary' }} px-3 py-2" wire:click="$set('filtroEstado', '1')">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> Por Asignar
                        </button>
                        <button type="button" class="btn {{ $filtroEstado == '2' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary border-opacity-25 text-secondary' }} px-3 py-2" wire:click="$set('filtroEstado', '2')">
                            <i class="bi bi-hourglass-split me-1"></i> En Gestión
                        </button>
                        <button type="button" class="btn {{ $filtroEstado == '3' ? 'btn-success fw-bold' : 'btn-outline-secondary border-opacity-25 text-secondary' }} px-3 py-2" wire:click="$set('filtroEstado', '3')">
                            <i class="bi bi-check-circle-fill me-1"></i> Resueltos
                        </button>
                        <button type="button" class="btn {{ $filtroEstado == 'todos' ? 'btn-primary fw-bold' : 'btn-outline-secondary border-opacity-25 text-secondary' }} px-3 py-2" wire:click="$set('filtroEstado', 'todos')">
                            <i class="bi bi-collection-fill me-1"></i> Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Tickets Premium --}}
    <div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4 position-relative">
        
        {{-- Indicador de Carga Overlay --}}
        <div wire:loading.flex class="position-absolute w-100 h-100 justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 10; top: 0; left: 0; backdrop-filter: blur(2px);">
            <div class="text-primary d-flex flex-column align-items-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <span class="mt-2 fw-semibold">Buscando tickets...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                <thead style="background: var(--bg-main);">
                    <tr class="text-nowrap">
                        <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="width: 10%;">ID Ticket</th>
                        <th class="py-3 border-0 text-muted small text-uppercase" style="width: 20%;">Cliente Reporta</th>
                        <th class="py-3 border-0 text-muted small text-uppercase" style="width: 30%;">Asunto Detallado</th>
                        @if($filtroEstado != '1')
                            <th class="py-3 border-0 text-muted small text-uppercase" style="width: 15%;">Técnico Asignado</th>
                        @endif
                        <th class="py-3 border-0 text-muted small text-uppercase" style="width: 10%;">Prioridad</th>
                        <th class="py-3 border-0 text-muted small text-uppercase text-end pe-4" style="width: 15%;">Acciones Operativas</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);" wire:key="ticket-{{ $ticket->id_ticket }}">
                        <td class="ps-4 py-3 fw-bold theme-text">#{{ $ticket->id_ticket }}</td>
                        <td class="py-3 fw-bold text-secondary">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                        <td class="py-3 text-muted text-wrap" style="max-width: 250px;" title="{{ $ticket->asunto }}">{{ Str::limit($ticket->asunto, 55) }}</td>
                        
                        @if($filtroEstado != '1')
                            <td class="py-3">
                                @if($ticket->estatus == 2 || $ticket->estatus == 3)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-person-circle me-1"></i> {{ $ticket->asignacion->tecnico->name ?? 'Sin Asignar' }}
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                        @endif

                        <td class="py-3">
                            @php
                                $prioridad = $ticket->prioridad->nombre_prioridad ?? 'N/A';
                                $badgeColor = match($prioridad) {
                                    'Crítica', 'Critica' => 'bg-danger text-white',
                                    'Alta' => 'bg-warning text-dark',
                                    'Media' => 'bg-info text-dark',
                                    'Baja' => 'bg-success text-white',
                                    default => 'bg-secondary text-white'
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} px-3 py-1 border-0 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                {{ $prioridad }}
                            </span>
                        </td>
                        <td class="text-end pe-4 py-3">
                            <div class="d-flex justify-content-end gap-2 align-items-center flex-nowrap">
                                @if($ticket->estatus == 1)
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold text-nowrap border-0" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                                        <i class="bi bi-person-plus-fill"></i> Asignar
                                    </button>
                                @elseif($ticket->estatus == 2)
                                    <button class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold text-dark text-nowrap border-0" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                        <i class="bi bi-arrow-repeat"></i> Reasignar
                                    </button>
                                @endif
                                
                                <a href="{{ route('soporte.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center border-secondary border-opacity-25" style="width: 32px; height: 32px; transition: all 0.2s;" title="Ver Detalles" onmouseover="this.classList.replace('btn-light', 'btn-info'); this.classList.add('text-white');" onmouseout="this.classList.replace('btn-info', 'btn-light'); this.classList.remove('text-white');">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="opacity-25 mb-3"><i class="bi bi-inbox fs-1 theme-text"></i></div>
                            <p class="text-muted fst-italic mb-0">No se encontraron tickets en esta categoría.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>

    {{-- Render modals here? No, Livewire might have issues with bootstraps modals changing the DOM, but let's include them inside the component so they can access $tickets variables! --}}
    @foreach($tickets as $ticket)
        @if($ticket->estatus == 1)
            @include('soporte.tickets.modals.asignar')
        @elseif($ticket->estatus == 2)
            @include('soporte.tickets.modals.reasignar')
        @endif
    @endforeach

</div>
