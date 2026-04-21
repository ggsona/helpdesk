@extends('layouts.admin')

@section('content')
<div class="card-premium">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Gestión Global de Tickets</h4>
            <p class="text-muted small">Supervisa y asigna los requerimientos entrantes</p>
        </div>
        <span class="badge bg-primary px-3 py-2">{{ $tickets->count() }} Tickets Totales</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Técnico Asignado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr>
                    <td><span class="fw-bold">#{{ $ticket->id }}</span></td>
                    <td>{{ $ticket->cliente->name }}</td>
                    <td>{{ $ticket->asunto }}</td>
                    <td>
                        @if($ticket->estado == 'abierto')
                            <span class="badge bg-danger">Pendiente</span>
                        @elseif($ticket->estado == 'asignado')
                            <span class="badge bg-info text-dark">En Proceso</span>
                        @else
                            <span class="badge bg-success">Resuelto</span>
                        @endif
                    </td>
                    <td>
                        {{ $ticket->tecnico->name ?? 'Sin asignar' }}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id }}">
                            <i class="bi bi-person-plus"></i> Asignar
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="asignarModal{{ $ticket->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('gestor.tickets.asignar', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Asignar Ticket #{{ $ticket->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-content p-3">
                                    <label class="form-label-custom">Seleccione un Técnico</label>
                                    <select name="id_tecnico" class="form-select form-control-premium" required>
                                        <option value="" selected disabled>Seleccionar...</option>
                                        @foreach($tecnicos as $tecnico)
                                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="submit" class="btn btn-primary px-4">Confirmar Asignación</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection