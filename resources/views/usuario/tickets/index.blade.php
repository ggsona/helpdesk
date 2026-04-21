<x-usuario-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold theme-text mb-1">Mis Tickets</h2>
            <p class="text-muted">Gestiona y revisa el estado de tus solicitudes técnicas.</p>
        </div>
        <a href="{{ route('usuario.tickets.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Ticket
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 100px;">ID</th>
                            <th class="py-3 border-0">Asunto / Categoría</th>
                            <th class="py-3 border-0 text-center">Fecha Creación</th>
                            <th class="py-3 border-0 text-center">Estado</th>
                            <th class="py-3 border-0 text-center">Prioridad</th>
                            <th class="py-3 border-0 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">
                                    #TK-{{ $ticket->id_ticket }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $ticket->asunto }}</span>
                                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">
                                            <i class="bi bi-tag me-1"></i>{{ $ticket->categoria->nombre_categoria ?? 'General' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center text-muted small">
                                    {{ $ticket->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    @if($ticket->estatus == 0)
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border px-3">Borrador</span>
                                    @else
                                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3">Enviado</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ticket->prioridad)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle small">
                                            <i class="bi bi-flag-fill me-1"></i> {{ $ticket->prioridad->nombre_prioridad }}
                                        </span>
                                    @else
                                        <span class="text-muted small italic">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Ver Detalle --}}
                                        <a href="{{ route('usuario.tickets.show', $ticket->id_ticket) }}" 
                                           class="btn btn-sm btn-outline-info border shadow-sm px-2" title="Ver Detalle">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        @if($ticket->estatus == 0)
                                            {{-- Enviar --}}
                                            <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success border shadow-sm px-2" title="Enviar ahora">
                                                    <i class="bi bi-send-fill"></i>
                                                </button>
                                            </form>

                                            {{-- Editar --}}
                                            <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" 
                                               class="btn btn-sm btn-outline-warning border shadow-sm px-2" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            {{-- Eliminar (Solo borradores) --}}
                                            <form action="{{ route('usuario.tickets.destroy', $ticket->id_ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este borrador?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border shadow-sm px-2" title="Eliminar">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-folder-x fs-1 opacity-25"></i>
                                    <p class="text-muted mt-2">No tienes tickets registrados aún.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-usuario-layout>