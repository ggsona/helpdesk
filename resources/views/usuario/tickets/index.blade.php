<x-usuario-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-body mb-1">Mis Tickets</h2>
            <p class="text-white-50">Gestiona y revisa el estado de tus solicitudes técnicas.</p>
        </div>
        <a href="{{ route('usuario.tickets.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Ticket
        </a>
    </div>

    {{-- Usamos 'card-premium' para soporte de temas --}}
    <div class="card card-premium shadow-sm border-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr class="text-white">
                            <th class="ps-4 py-3 border-0 small text-uppercase" style="width: 100px;">ID</th>
                            <th class="py-3 border-0 small text-uppercase">Asunto / Categoría</th>
                            <th class="py-3 border-0 text-center small text-uppercase">Fecha Creación</th>
                            <th class="py-3 border-0 text-center small text-uppercase">Estado</th>
                            <th class="py-3 border-0 text-center small text-uppercase">Prioridad</th>
                            <th class="py-3 border-0 text-end pe-4 small text-uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($tickets as $ticket)
                            <tr class="border-bottom border-light-subtle">
                                <td class="ps-4 fw-bold text-primary">
                                    #TK-{{ $ticket->id_ticket }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-body">{{ $ticket->asunto }}</span>
                                        <small class="text-uppercase" style="font-size: 0.7rem;">
                                            <i class="bi bi-tag me-1 text-primary"></i>{{ $ticket->categoria->nombre_categoria ?? 'General' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center small">
                                    {{ $ticket->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    @php
                                        // Definimos el color del badge según el estatus numérico
                                        $colorEstatus = match($ticket->estatus) {
                                            0 => 'secondary', // Borrador
                                            1 => 'primary',   // Abierto
                                            2 => 'warning',   // En Proceso
                                            3 => 'success',   // Resuelto
                                            4 => 'dark',      // Cerrado
                                            default => 'info',
                                        };
                                    @endphp

                                    <span class="badge rounded-pill bg-{{ $colorEstatus }}-subtle text-{{ $colorEstatus }}-emphasis border border-{{ $colorEstatus }}-subtle px-3">
                                        {{ $ticket->estado_texto }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($ticket->prioridad)
                                        @php
                                            $prioridad = strtolower($ticket->prioridad->nombre_prioridad);
                                            $colorClass = 'bg-secondary';
                                            if(str_contains($prioridad, 'crítica')) $colorClass = 'bg-danger';
                                            elseif(str_contains($prioridad, 'alta')) $colorClass = 'bg-warning text-dark';
                                            elseif(str_contains($prioridad, 'media')) $colorClass = 'bg-info text-dark';
                                            elseif(str_contains($prioridad, 'baja')) $colorClass = 'bg-success';
                                        @endphp
                                        <span class="badge {{ $colorClass }} border border-light-subtle small px-3">
                                            <i class="bi bi-flag-fill me-1"></i> {{ $ticket->prioridad->nombre_prioridad }}
                                        </span>
                                    @else
                                        <span class="text-white-60 small fst-italic">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Botón Ver --}}
                                        <a href="{{ route('usuario.tickets.show', $ticket->id_ticket) }}" 
                                           class="btn btn-sm btn-outline-info border-info-subtle shadow-sm px-2" title="Ver Detalle">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        @if($ticket->estatus == 0)
                                            {{-- Botón Enviar --}}
                                            <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success border-success-subtle shadow-sm px-2" title="Enviar ahora">
                                                    <i class="bi bi-send-fill"></i>
                                                </button>
                                            </form>

                                            {{-- Botón Editar --}}
                                            <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" 
                                               class="btn btn-sm btn-outline-warning border-warning-subtle shadow-sm px-2" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            {{-- Botón Eliminar --}}
                                            <form action="{{ route('usuario.tickets.destroy', $ticket->id_ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este borrador?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle shadow-sm px-2" title="Eliminar">
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
                                    <i class="bi bi-folder-x fs-1 text-white opacity-25"></i>
                                    <p class="text-white mt-2 fst-italic">No tienes tickets registrados aún.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-usuario-layout>