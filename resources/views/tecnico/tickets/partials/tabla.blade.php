<div class="table-responsive">
    <table class="table align-middle mb-0" style="color: var(--text-main);">
        <thead style="border-bottom: 2px solid var(--border-color);">
            <tr>
                <th class="ps-2" style="width: 10%; color: var(--text-muted);">ID</th>
                <th style="width: 30%; color: var(--text-muted);">Asunto / Usuario</th>
                <th style="width: 25%; color: var(--text-muted);">Categoría / Equipo</th>
                @if(isset($esResuelto) && $esResuelto)
                    <th style="width: 25%; color: var(--text-muted);">Solución</th>
                @else
                    <th style="width: 15%; color: var(--text-muted);">Estado</th>
                @endif
                <th class="text-end pe-2" style="color: var(--text-muted);">Acción</th>
            </tr>
        </thead>
        <tbody style="border: none;">
            @forelse($tickets as $ticket)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td class="ps-2">
                        <span class="badge" style="background: var(--bg-main); color: var(--text-main);">#{{ $ticket->id_ticket }}</span>
                    </td>
                    <td>
                        <div class="fw-bold ticket-asunto">{{ $ticket->asunto }}</div>
                        <small style="color: var(--text-muted);">{{ $ticket->usuario->name }}</small>
                    </td>
                    <td>
                        <div class="small fw-semibold text-primary">{{ $ticket->categoria->nombre_categoria ?? 'S/C' }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'Genérico' }}
                        </div>
                    </td>
                    
                    @if(isset($esResuelto) && $esResuelto)
                        <td>
                            <div class="small italic text-success">
                                <i class="bi bi-journal-check me-1"></i>
                                {{ Str::limit($ticket->solucion->resumen_usuario ?? 'Cerrado satisfactoriamente', 45) }}
                            </div>
                        </td>
                    @else
                        <td>
                            @php
                                $colorStatus = match($ticket->id_prioridad) {
                                    4 => '#dc3545', // Rojo
                                    3 => '#ffc107', // Amarillo
                                    2 => '#0dcaf0', // Cian
                                    1 => '#198754', // Verde
                                    default => '#6c757d'
                                };
                            @endphp
                            <span class="badge" style="border: 1px solid {{ $colorStatus }}; color: {{ $colorStatus }}; background: transparent;">
                                {{ $ticket->prioridad->nombre_prioridad }}
                            </span>
                        </td>
                    @endif

                    <td class="text-end pe-2">
                        <a href="{{ route('tecnico.tickets.show', $ticket->id_ticket) }}" 
                           class="btn btn-sm px-3 rounded-pill"
                           style="border: 1px solid var(--border-color); color: var(--text-main); background: var(--bg-main);">
                            <i class="bi bi-eye me-1"></i> Ver
                        </a>
                        {{-- Botón para ir directo a la Solución --}}
                        <a href="{{ route('tecnico.tickets.resolver', $ticket->id_ticket) }}" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                            <i class="bi bi-check2-circle"></i> Resolver
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4" style="color: var(--text-muted);">
                        No hay registros disponibles.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>