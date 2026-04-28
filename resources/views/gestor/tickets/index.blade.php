@extends('layouts.admin')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            {{-- Usamos card-premium para que el fondo se adapte al tema --}}
            <div class="card-premium shadow-sm border-0">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-ticket-perforated me-2"></i> Panel de Gestión de Tickets
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="pills-container mb-4 shadow-sm py-2 px-4 rounded-3" style="background: var(--bg-main);">
                        <ul class="nav nav-pills nav-fill gap-2 pr-2" id="pills-tab" role="tablist">
                            
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

                    <div class="tab-content" id="pills-tabContent">
                        {{-- TAB: NUEVOS --}}
                        <div class="tab-pane fade show active" id="pills-nuevos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="text-custom-muted">
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Asunto</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketsNuevos as $ticket)
                                            <tr class="border-bottom border-secondary border-opacity-10">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td class="fw-medium">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                                {{-- Eliminado text-white-50 por text-custom-muted --}}
                                                <td class="text-custom-muted">{{ Str::limit($ticket->asunto, 40) }}</td>
                                                <td class="small text-custom-muted">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                                                            <i class="bi bi-person-plus me-1"></i> Asignar
                                                        </button>
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-info border-opacity-25">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('gestor.tickets.modals.asignar')
                                        @empty
                                            <tr><td colspan="5" class="text-center py-5 text-custom-muted italic">No hay tickets pendientes.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: EN PROCESO (Con Prioridades de Color) --}}
                        <div class="tab-pane fade" id="pills-proceso" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="text-custom-muted">
                                            <th>ID</th>
                                            <th>Técnico</th>
                                            <th>Asunto</th>
                                            <th>Prioridad</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketsAsignados as $ticket)
                                            <tr class="border-bottom border-secondary border-opacity-10">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">
                                                        <i class="bi bi-person me-1"></i> {{ $ticket->asignacion->tecnico->name ?? 'Sin Técnico' }}
                                                    </span>
                                                </td>
                                                <td class="text-custom-muted">{{ Str::limit($ticket->asunto, 40) }}</td>
                                                <td>
                                                    @php
                                                        $prioridad = $ticket->prioridad->nombre_prioridad ?? 'N/A';
                                                        $badgeColor = match($prioridad) {
                                                            'Crítica', 'Critica' => 'bg-danger',
                                                            'Alta' => 'bg-warning text-dark',
                                                            'Media' => 'bg-primary',
                                                            'Baja' => 'bg-success',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeColor }} shadow-sm px-2">
                                                        {{ $prioridad }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-warning px-3 shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                                            <i class="bi bi-arrow-repeat me-1"></i> Reasignar
                                                        </button>
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-info border-opacity-25">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('gestor.tickets.modals.reasignar')
                                        @empty
                                            <tr><td colspan="5" class="text-center py-5 text-custom-muted">No hay tickets en proceso.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: RESUELTOS --}}
                        <div class="tab-pane fade" id="pills-resueltos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="text-custom-muted">
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Asunto</th>
                                            <th>Técnico</th>
                                            <th>Fecha Cierre</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketsResueltos as $ticket)
                                            <tr class="border-bottom border-secondary border-opacity-10">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td class="fw-medium">{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                                <td class="text-custom-muted">{{ Str::limit($ticket->asunto, 35) }}</td>
                                                <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ $ticket->asignacion->tecnico->name ?? 'N/A' }}</span></td>
                                                <td class="text-custom-muted small">{{ $ticket->updated_at->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-info px-3">
                                                            <i class="bi bi-info-circle me-1"></i> Detalles
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center py-5 text-custom-muted">No hay casos resueltos.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection