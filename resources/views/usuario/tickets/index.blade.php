<x-usuario-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold theme-text mb-1">Mis Tickets</h2>
            <p class="theme-muted mb-0">Gestiona y revisa el estado de tus solicitudes técnicas.</p>
        </div>
        <a href="{{ route('usuario.tickets.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Ticket
        </a>
    </div>

    {{-- Card Premium: Fondo adaptable y bordes suaves --}}
    <div class="card-premium shadow-sm overflow-hidden border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                {{-- Cabecera con fondo sutil adaptable --}}
                <thead style="background: var(--bg-main);">
                    <tr>
                        <th class="ps-4 py-3 border-0 theme-muted small text-uppercase" style="width: 120px;">ID</th>
                        <th class="py-3 border-0 theme-muted small text-uppercase">Asunto / Categoría</th>
                        <th class="py-3 border-0 text-center theme-muted small text-uppercase">Fecha</th>
                        <th class="py-3 border-0 text-center theme-muted small text-uppercase">Estado</th>
                        <th class="py-3 border-0 text-center theme-muted small text-uppercase">Prioridad</th>
                        <th class="py-3 border-0 text-end pe-4 theme-muted small text-uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td class="ps-4 fw-bold text-primary">
                                #TK-{{ $ticket->id_ticket }}
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold theme-text">{{ $ticket->asunto }}</span>
                                    <small class="theme-muted text-uppercase" style="font-size: 0.7rem;">
                                        <i class="bi bi-tag me-1 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'General' }}
                                    </small>
                                </div>
                            </td>
                            <td class="text-center small theme-text">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>
                            <td class="text-center">
                                @php
                                    $colorEstatus = match($ticket->estatus) {
                                        0 => 'secondary',
                                        1 => 'primary',
                                        2 => 'warning',
                                        3 => 'success',
                                        4 => 'dark',
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
                                        $prioridadNom = strtolower($ticket->prioridad->nombre_prioridad);
                                        $colorPrio = 'secondary';
                                        if(str_contains($prioridadNom, 'crítica')) $colorPrio = 'danger';
                                        elseif(str_contains($prioridadNom, 'alta')) $colorPrio = 'warning';
                                        elseif(str_contains($prioridadNom, 'media')) $colorPrio = 'info';
                                        elseif(str_contains($prioridadNom, 'baja')) $colorPrio = 'success';
                                    @endphp
                                    {{-- Usamos subclases -subtle para un color intermedio elegante --}}
                                    <span class="badge bg-{{ $colorPrio }}-subtle text-{{ $colorPrio }}-emphasis border border-{{ $colorPrio }}-subtle px-3 py-2 small" 
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ strtoupper($ticket->prioridad->nombre_prioridad) }}
                                    </span>
                                @else
                                    <span class="theme-muted small fst-italic">Por asignar</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Botón Detalles (Principal) --}}
                                    <a href="{{ route('usuario.tickets.show', $ticket->id_ticket) }}" 
                                    class="btn btn-sm btn-outline-primary border-primary-subtle px-3 rounded-3 d-flex align-items-center shadow-sm" 
                                    title="Ver Detalle" style="transition: 0.2s; font-weight: 500;">
                                        <i class="bi bi-eye-fill me-2"></i> Detalles
                                    </a>

                                    @if($ticket->estatus == 0)
                                        {{-- Botón Enviar --}}
                                        <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success border-success-subtle px-2 rounded-3 shadow-sm" 
                                                    title="Enviar ahora">
                                                <i class="bi bi-send-fill"></i>
                                            </button>
                                        </form>

                                        {{-- Botón Editar --}}
                                        <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" 
                                        class="btn btn-sm btn-outline-warning border-warning-subtle px-2 rounded-3 shadow-sm" 
                                        title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        {{-- Botón Eliminar --}}
                                        <form action="{{ route('usuario.tickets.destroy', $ticket->id_ticket) }}" method="POST" class="d-inline" 
                                            onsubmit="return confirm('¿Eliminar este borrador?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm" 
                                                    title="Eliminar">
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
                                <div class="opacity-25 mb-3">
                                    <i class="bi bi-folder2-open display-1 theme-text"></i>
                                </div>
                                <p class="theme-muted fst-italic">No tienes tickets registrados aún.</p>
                                <a href="{{ route('usuario.tickets.create') }}" class="btn btn-sm btn-primary rounded-pill">Crea tu primero aquí</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-usuario-layout>