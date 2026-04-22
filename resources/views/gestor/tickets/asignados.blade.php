@extends('layouts.admin')

@section('content')
<div class="card-premium p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="bi bi-globe2 me-2"></i>Gestión Global de Tickets
            </h4>
            <p class="text-muted small">Supervisa y asigna los requerimientos entrantes del sistema</p>
        </div>
        <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $tickets->count() }} Tickets Totales</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Técnico Asignado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr>
                    <td><span class="fw-bold text-dark">#{{ $ticket->id_ticket ?? $ticket->id }}</span></td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">{{ $ticket->usuario->name }}</span>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-building text-muted" style="font-size: 0.7rem;"></i>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    {{-- Cadena: Usuario -> Persona -> Oficina --}}
                                    {{ $ticket->usuario->persona->oficina->nombre_oficina ?? 'Sin oficina' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>{{ Str::limit($ticket->asunto, 40) }}</td>
                    <td>
                        @if($ticket->estatus == 1 || $ticket->estado == 'abierto')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Pendiente</span>
                        @elseif($ticket->estatus == 2 || $ticket->estado == 'asignado')
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2">En Proceso</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Resuelto</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-badge me-2 text-muted"></i>
                            <span class="{{ $ticket->tecnico ? 'fw-bold text-primary' : 'text-muted italic' }}">
                                {{ $ticket->tecnico->name ?? 'Sin asignar' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-warning px-3 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#reasignarModal{{ $ticket->id_ticket }}">
                                <i class="bi bi-arrow-repeat me-1"></i> Reasignar
                            </button>
                            <a href="{{ route('gestor.tickets.show', $ticket->id_ticket) }}" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="reasignarModal{{ $ticket->id_ticket }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="{{ route('gestor.tickets.asignar', $ticket->id_ticket) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title fw-bold text-dark">Reasignar Ticket #{{ $ticket->id_ticket }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info small">
                                        <i class="bi bi-info-circle me-2"></i> Actualmente asignado a: <strong>{{ $ticket->tecnico->name ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nuevo Técnico</label>
                                        <select name="id_usuario_tecnico" class="form-select" required>
                                            @foreach($tecnicos as $tecnico)
                                                <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Motivo de la Reasignación</label>
                                        <textarea name="nota" class="form-control border-danger" rows="3" required placeholder="Explique por qué se está cambiando de técnico..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="submit" class="btn btn-warning px-4">Confirmar Cambio</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection