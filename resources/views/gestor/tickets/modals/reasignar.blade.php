<div class="modal fade" id="reasignarModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('gestor.tickets.asignar', $ticket->id_ticket) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">Reasignar Técnico Ticket #{{ $ticket->id_ticket }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-2"></i> El ticket cambiará de responsable.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Motivo del Cambio (Requerido)</label>
                        <textarea name="nota" class="form-control border-danger" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo Técnico</label>
                        <select name="id_usuario_tecnico" class="form-select" required>
                            <option value="" selected disabled>Seleccione un técnico...</option>
                            @foreach($tecnicos as $tecnico)
                                @php
                                    // Verificamos si existe la asignación y si coincide con el técnico del loop
                                    $esActual = $ticket->asignacion && $ticket->asignacion->id_usuario_tecnico == $tecnico->id;
                                @endphp
                                
                                <option value="{{ $tecnico->id }}" {{ $esActual ? 'disabled' : '' }}>
                                    {{ $tecnico->name }} {{ $esActual ? '(Actual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Mantenemos la prioridad actual --}}
                    <input type="hidden" name="id_prioridad" value="{{ $ticket->id_prioridad }}">
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-warning w-100 fw-bold">Confirmar Reasignación</button>
                </div>
            </form>
        </div>
    </div>
</div>