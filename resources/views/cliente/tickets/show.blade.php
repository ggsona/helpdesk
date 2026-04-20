<x-cliente-layout>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('cliente.tickets.index') }}" class="btn btn-link text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2 class="fw-bold mb-0">Ticket #{{ $ticket->id_ticket }}</h2>
                <span class="badge {{ $ticket->estatus == 0 ? 'bg-secondary' : 'bg-primary' }} fs-6">
                    {{ $ticket->estatus == 0 ? 'Borrador' : 'Enviado' }}
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Asunto: {{ $ticket->asunto }}</h5>
                        <p class="text-muted border-start border-4 ps-3 py-2 bg-light">
                            {{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}
                        </p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-paperclip me-2"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-6">
                                    <div class="card h-100 border-light shadow-xs">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 120px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-transparent border-0 text-center pb-3">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-light border small">
                                                <i class="bi bi-download"></i> Bajar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="text-muted mb-0 small">No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4">Seguimiento de Soporte</h6>
                        
                        {{-- Nuevo: Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="text-muted small d-block">Técnico Responsable</label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center mt-2 p-2 bg-light rounded border border-dashed">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        {{ substr($ticket->tecnico->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block" style="font-size: 0.9rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-muted">Soporte Técnico</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-warning small italic"><i class="bi bi-clock-history me-1"></i>Esperando asignación...</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small d-block">Categoría</label>
                            <span class="fw-bold">{{ $ticket->categoria->nombre_categoria ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">Equipo</label>
                            <span class="fw-bold">{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small d-block">Prioridad Asignada</label>
                            @if($ticket->prioridad)
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="text-muted small">Por definir por el gestor</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-cliente-layout>