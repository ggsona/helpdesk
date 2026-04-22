@extends('layouts.admin')

@section('content')

<style>
    /* Ajuste para modo oscuro y píldoras */
    .nav-pills .nav-link {
        color: #adb5bd; 
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    .pills-container {
        background: rgba(255, 255, 255, 0.05);
        padding: 5px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    /* Ajuste de tablas para modo oscuro */
    .table { color: #e9ecef; }
    .table-light { background-color: rgba(255, 255, 255, 0.05) !important; color: #fff; border-bottom: 1px solid #444; }
    .card { background-color: #1a1d20; border: 1px solid #373b3e; }
    .text-muted-custom { color: #adb5bd !important; }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-secondary">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-ticket-perforated me-2"></i> Panel de Gestión de Tickets
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="pills-container mb-4 shadow-sm">
                        <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="pills-nuevos-tab" data-bs-toggle="pill" data-bs-target="#pills-nuevos" type="button" role="tab">
                                    <i class="bi bi-plus-circle me-2"></i> Por Asignar
                                    <span class="badge bg-primary ms-2">{{ $ticketsNuevos->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="pills-proceso-tab" data-bs-toggle="pill" data-bs-target="#pills-proceso" type="button" role="tab">
                                    <i class="bi bi-clock-history me-2"></i> En Gestión
                                    <span class="badge bg-warning text-dark ms-2">{{ $ticketsAsignados->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="pills-resueltos-tab" data-bs-toggle="pill" data-bs-target="#pills-resueltos" type="button" role="tab">
                                    <i class="bi bi-check-all me-2"></i> Resueltos
                                    <span class="badge bg-success ms-2">{{ $ticketsResueltos->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        {{-- NUEVOS --}}
                        <div class="tab-pane fade show active" id="pills-nuevos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Asunto</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketsNuevos as $ticket)
                                            <tr class="border-secondary">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td>{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                                <td class="text-white-50">{{ Str::limit($ticket->asunto, 40) }}</td>
                                                <td class="small">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id_ticket }}">
                                                            <i class="bi bi-person-plus"></i> Asignar
                                                        </button>
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('gestor.tickets.modals.asignar')
                                        @empty
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No hay tickets pendientes de asignación.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- EN PROCESO --}}
                        <div class="tab-pane fade" id="pills-proceso" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Técnico Asignado</th>
                                            <th>Asunto</th>
                                            <th>Prioridad</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketsAsignados as $ticket)
                                            <tr class="border-secondary">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $ticket->asignacion->tecnico->name ?? 'Sin Técnico' }}</span></td>
                                                <td class="text-white-50">{{ Str::limit($ticket->asunto, 40) }}</td>
                                                <td>
                                                    <span class="badge {{ ($ticket->prioridad->nombre_prioridad ?? '') == 'Alta' ? 'bg-danger' : 'bg-primary' }}">
                                                        {{ $ticket->prioridad->nombre_prioridad ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-warning px-3" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                                            <i class="bi bi-arrow-repeat"></i> Reasignar
                                                        </button>
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('gestor.tickets.modals.reasignar')
                                        @empty
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No hay tickets en proceso actualmente.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- RESUELTOS --}}
                        <div class="tab-pane fade" id="pills-resueltos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
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
                                            <tr class="border-secondary">
                                                <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                                                <td>{{ $ticket->usuario->name ?? 'N/A' }}</td>
                                                <td class="text-white-50">{{ Str::limit($ticket->asunto, 35) }}</td>
                                                <td><span class="badge bg-success">{{ $ticket->asignacion->tecnico->name ?? 'N/A' }}</span></td>
                                                <td>{{ $ticket->updated_at->format('d/m/Y') }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-success px-3" data-bs-toggle="modal" data-bs-target="#verResueltoModal{{ $ticket->id_ticket }}">
                                                            <i class="bi bi-info-circle"></i> Detalles
                                                        </button>
                                                        <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center py-5 text-muted">No hay casos resueltos todavía.</td></tr>
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