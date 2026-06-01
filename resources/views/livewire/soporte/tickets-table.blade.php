<div>
    {{-- Navegación y Filtros --}}
    <div class="row mb-4">
        <div class="col-md-5">
            <div class="input-group shadow-sm rounded-3">
                <span class="input-group-text bg-white border-end-0 border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 border-secondary border-opacity-25" placeholder="Buscar por ID, asunto o cliente...">
            </div>
        </div>
        <div class="col-md-7 d-flex justify-content-end">
            <div class="btn-group shadow-sm" role="group">
                <button type="button" class="btn {{ $filtroEstado == '1' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}" wire:click="$set('filtroEstado', '1')">
                    <i class="bi bi-plus-circle me-1"></i> Por Asignar
                </button>
                <button type="button" class="btn {{ $filtroEstado == '2' ? 'btn-warning fw-bold text-dark' : 'btn-outline-secondary' }}" wire:click="$set('filtroEstado', '2')">
                    <i class="bi bi-clock-history me-1"></i> En Gestión
                </button>
                <button type="button" class="btn {{ $filtroEstado == '3' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}" wire:click="$set('filtroEstado', '3')">
                    <i class="bi bi-check-all me-1"></i> Resueltos
                </button>
                <button type="button" class="btn {{ $filtroEstado == 'todos' ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}" wire:click="$set('filtroEstado', 'todos')">
                    <i class="bi bi-collection me-1"></i> Todos
                </button>
            </div>
        </div>
    </div>

    {{-- Indicador de Carga --}}
    <div wire:loading class="w-100 text-center py-4 text-primary">
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <span class="ms-2 fw-semibold">Buscando tickets...</span>
    </div>

    {{-- Tabla de Tickets --}}
    <div class="table-responsive" wire:loading.class="opacity-50">
        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
            <thead>
                <tr class="text-custom-muted">
                    <th style="width: 10%;">ID</th>
                    <th style="width: 20%;">Cliente</th>
                    <th style="width: 30%;">Asunto</th>
                    @if($filtroEstado != '1')
                        <th style="width: 15%;">Técnico</th>
                    @endif
                    <th style="width: 10%;">Prioridad</th>
                    <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr class="border-bottom border-secondary border-opacity-10" wire:key="ticket-{{ $ticket->id_ticket }}">
                        <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                        <td class="fw-bold theme-text">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                        <td class="text-muted">{{ Str::limit($ticket->asunto, 40) }}</td>
                        
                        @if($filtroEstado != '1')
                            <td>
                                @if($ticket->estatus == 2 || $ticket->estatus == 3)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-person-circle me-1"></i> {{ $ticket->asignacion->tecnico->name ?? 'Sin Asignar' }}
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                        @endif

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
                            <span class="badge {{ $badgeColor }} px-3 py-1 border-0 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                {{ $prioridad }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                @if($ticket->estatus == 1)
                                    <button class="btn btn-sm btn-primary px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                                        <i class="bi bi-person-plus-fill"></i> Asignar
                                    </button>
                                @elseif($ticket->estatus == 2)
                                    <button class="btn btn-sm btn-warning text-dark px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1 fw-bold" style="height: 32px;" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                        <i class="bi bi-arrow-repeat"></i> Reasignar
                                    </button>
                                @endif
                                
                                <a href="{{ route('soporte.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-info border-info-subtle px-0 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Ver Detalles">
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
