<div class="modal fade" id="verResueltoModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-all me-2"></i> Ticket Finalizado #{{ $ticket->id_ticket }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="badge bg-light text-success p-3 rounded-circle mb-2">
                        <i class="bi bi-person-check-fill fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Resuelto por: {{ $ticket->asignacion->tecnico->name ?? 'N/A' }}</h6>
                    <small class="text-muted">Finalizado el {{ $ticket->updated_at->format('d/m/Y h:i A') }}</small>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="fw-bold small text-muted d-block mb-1">Asunto Original:</label>
                    <p class="bg-light p-2 rounded border-start border-4 border-success">{{ $ticket->asunto }}</p>
                </div>

                {{-- Aquí podrías mostrar el último comentario del técnico (la solución) --}}
                <div class="mb-0">
                    <label class="fw-bold small text-muted d-block mb-1">Nota de Cierre:</label>
                    <div class="p-2 border rounded italic text-secondary bg-light">
                        @php
                            $ultimoComentario = $ticket->comentarios()->where('id_usuario', $ticket->asignacion->id_usuario_tecnico)->latest()->first();
                        @endphp
                        {{ $ultimoComentario->mensaje ?? 'No se dejó una nota de cierre específica.' }}
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar Vista Rápida</button>
            </div>
        </div>
    </div>
</div>