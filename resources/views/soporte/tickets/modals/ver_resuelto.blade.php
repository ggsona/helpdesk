<div class="modal fade" id="verResueltoModal{{ $ticket->id_ticket }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-dark text-white" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-success text-white p-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Caso Finalizado</h5>
                        <small class="opacity-75">Ticket #{{ $ticket->id_ticket }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="badge bg-success bg-opacity-10 text-success p-3 rounded-circle mb-2">
                        <i class="bi bi-person-check-fill fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-white">Resuelto por: {{ $ticket->asignacion->tecnico->name ?? 'N/A' }}</h6>
                    <small class="text-muted">Finalizado el {{ $ticket->updated_at->format('d/m/Y h:i A') }}</small>
                </div>

                <hr class="opacity-25 my-4">

                <div class="mb-3">
                    <label class="fw-bold small text-secondary text-uppercase d-block mb-2" style="font-size: 0.65rem; letter-spacing: 0.5px;">Asunto Original</label>
                    <p class="p-3 rounded border-start border-4 border-success bg-black bg-opacity-50 text-white mb-0" style="font-size: 0.9rem;">{{ $ticket->asunto }}</p>
                </div>

                {{-- Mostrar la solución publicada para el usuario final --}}
                <div class="mb-0">
                    <label class="fw-bold small text-secondary text-uppercase d-block mb-2" style="font-size: 0.65rem; letter-spacing: 0.5px;">Nota de Cierre Publicada</label>
                    <div class="p-3 border border-secondary border-opacity-25 rounded bg-black bg-opacity-50 text-success fst-italic" style="font-size: 0.9rem;">
                        <i class="bi bi-journal-check me-2"></i>
                        {{ $ticket->solucion->resumen_usuario ?? 'Cerrado satisfactoriamente sin comentarios adicionales.' }}
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 bg-black bg-opacity-25" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 fw-bold shadow-sm" data-bs-dismiss="modal">Cerrar Vista Rápida</button>
            </div>
        </div>
    </div>
</div>
