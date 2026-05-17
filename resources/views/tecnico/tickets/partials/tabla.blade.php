<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr class="text-custom-muted">
                <th style="width: 10%;">ID</th>
                <th style="width: 35%;">Asunto / Usuario</th>
                <th style="width: 25%;">Categoría / Equipo</th>
                @if(isset($esResuelto) && $esResuelto)
                    <th style="width: 20%;">Solución</th>
                @else
                    <th style="width: 15%;">Estado</th>
                @endif
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr class="border-bottom border-secondary border-opacity-10">
                    <td class="fw-bold text-primary">#{{ $ticket->id_ticket }}</td>
                    <td>
                        <div class="fw-bold text-main">{{ $ticket->asunto }}</div>
                        <small class="text-custom-muted">{{ $ticket->usuario->name ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <div class="small fw-semibold text-primary">
                            <i class="bi bi-tag me-1"></i>{{ $ticket->categoria->nombre_categoria ?? 'S/C' }}
                        </div>
                        <div class="small text-custom-muted">
                            {{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'Genérico' }}
                        </div>
                    </td>
                    
                    @if(isset($esResuelto) && $esResuelto)
                        <td>
                            <div class="small italic text-success">
                                <i class="bi bi-journal-check me-1"></i>
                                {{ Str::limit($ticket->solucion->resumen_usuario ?? 'Cerrado satisfactoriamente', 40) }}
                            </div>
                        </td>
                    @else
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
                    @endif

                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('tecnico.tickets.show', $ticket->id_ticket) }}" 
                               class="btn btn-sm btn-info border-opacity-25 justify-content-center" title="Ver Detalles">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @if($ticket->estatus == 2)
                                <a href="{{ route('tecnico.tickets.resolver', $ticket->id_ticket) }}" 
                                   class="btn btn-sm btn-success px-3 shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Resolver
                                </a>
                            @elseif($ticket->estatus == 3)
                                <a href="{{ route('tecnico.tickets.editar-solucion', $ticket->id_ticket) }}" 
                                   class="btn btn-sm btn-warning px-3 shadow-sm text-dark">
                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-custom-muted italic">
                        No hay registros disponibles.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>