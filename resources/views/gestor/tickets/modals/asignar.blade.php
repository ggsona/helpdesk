<div class="modal fade" id="asignarModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-dark text-white">
            {{-- Header con degradado --}}
            <div class="modal-header border-0 bg-primary text-white p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bi bi-person-gear fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Gestionar Responsable</h5>
                        <small class="opacity-75">Ticket #{{ $ticket->id_ticket }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('gestor.tickets.asignar', $ticket->id_ticket) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 small mb-4 text-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        @if($ticket->asignacion)
                            Este ticket ya está asignado a <strong>{{ $ticket->asignacion->tecnico->name }}</strong>. Al seleccionar otro técnico, se notificará el cambio.
                        @else
                            Selecciona un técnico especializado y define la prioridad para atender este requerimiento.
                        @endif
                    </div>

                    {{-- Selección de Técnico --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Técnico Especialista</label>
                        <select name="id_usuario_tecnico" class="form-select border-secondary bg-black text-white p-3 shadow-none" required>
                            <option value="" selected disabled>Seleccionar técnico...</option>
                            @foreach($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}" {{ ($ticket->asignacion->id_usuario_tecnico ?? '') == $tecnico->id ? 'selected' : '' }}>
                                    {{ $tecnico->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NUEVO: Selección de Prioridad --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Prioridad del Ticket</label>
                        <select name="id_prioridad" class="form-select border-secondary bg-black text-white p-3 shadow-none" required>
                            <option value="" selected disabled>Definir prioridad...</option>
                            @foreach($prioridades as $prioridad)
                                {{-- Usamos id_prioridad que es el nombre real en tu DB --}}
                                <option value="{{ $prioridad->id_prioridad }}" {{ $ticket->id_prioridad == $prioridad->id_prioridad ? 'selected' : '' }}>
                                    {{ $prioridad->nombre_prioridad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Nota de Instrucción (Opcional)</label>
                        <textarea name="nota" class="form-control border-secondary bg-black text-white p-3 shadow-none" rows="3" placeholder="Escribe aquí alguna indicación técnica para el compañero..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 bg-black bg-opacity-25">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm rounded-pill">
                        <i class="bi bi-check2-circle me-1"></i> Confirmar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>