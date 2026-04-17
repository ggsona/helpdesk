<x-cliente-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold theme-text mb-1">Mis Tickets</h2>
            <p class="text-muted">Gestiona y revisa el estado de tus solicitudes técnicas.</p>
        </div>
        <a href="{{ route('cliente.tickets.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Ticket
        </a>
    </div>

    <div class="card-premium shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light theme-bg-dark">
                        <tr>
                            <th class="ps-4 py-3 border-0">ID</th>
                            <th class="py-3 border-0">Asunto / Categoría</th>
                            <th class="py-3 border-0 text-center">Estado</th>
                            <th class="py-3 border-0 text-center">Prioridad</th>
                            <th class="py-3 border-0 text-end pe-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#TK-{{ $ticket->id }}</td>
                            <td>
                                <div class="fw-bold theme-text">{{ $ticket->asunto }}</div>
                                <small class="text-muted">{{ $ticket->categoria->nombre_categoria }}</small>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = match($ticket->estado) {
                                        'Abierto' => 'bg-info-subtle text-info',
                                        'En Proceso' => 'bg-warning-subtle text-warning',
                                        'Resuelto' => 'bg-success-subtle text-success',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $statusClass }} px-3 py-2">
                                    {{ $ticket->estado }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="small fw-medium theme-text">
                                    <i class="bi bi-flag-fill me-1"></i>{{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border rounded-circle" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" width="80" class="opacity-25 mb-3">
                                <p class="text-muted">Aún no has creado ningún ticket.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-cliente-layout>

