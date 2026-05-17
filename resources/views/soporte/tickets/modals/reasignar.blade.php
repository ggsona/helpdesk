<div class="modal fade" id="reasignarModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-dark text-white" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-warning text-dark p-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bi bi-arrow-repeat fs-4 text-dark"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark">Reasignar Técnico</h5>
                        <small class="text-dark opacity-75">Ticket #{{ $ticket->id_ticket }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('soporte.tickets.asignar', $ticket->id_ticket) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-0 small mb-4 text-warning-emphasis d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <strong>Atención:</strong> El ticket cambiará de responsable. El nuevo especialista recibirá una alerta y el cambio se registrará en el chat de auditoría.
                        </div>
                    </div>

                    {{-- Motivo del Cambio --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary text-danger">Motivo de la Reasignación (Requerido)</label>
                        <textarea name="nota" class="form-control border-danger bg-black text-white p-3 shadow-none rounded-3" rows="3" placeholder="Explica por qué se realiza el cambio de especialista..." required></textarea>
                    </div>

                    {{-- Selección del Nuevo Técnico --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Nuevo Técnico Especialista</label>
                        <select name="id_usuario_tecnico" class="form-select border-secondary bg-black text-white p-3 shadow-none rounded-3" required>
                            <option value="" selected disabled>Seleccionar nuevo técnico...</option>
                            @foreach($tecnicos as $tecnico)
                                @php
                                    $esActual = $ticket->asignacion && $ticket->asignacion->id_usuario_tecnico == $tecnico->id;
                                @endphp
                                <option value="{{ $tecnico->id }}" {{ $esActual ? 'disabled' : '' }}>
                                    {{ $tecnico->name }} {{ $esActual ? '(Actual - No disponible)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mantenemos la prioridad actual en el campo oculto --}}
                    <input type="hidden" name="id_prioridad" value="{{ $ticket->id_prioridad }}">
                </div>

                <div class="modal-footer border-0 p-4 bg-black bg-opacity-25" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm rounded-pill">
                        <i class="bi bi-check2-circle me-1"></i> Confirmar Reasignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
