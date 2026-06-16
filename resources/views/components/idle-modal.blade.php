<!-- Modal de Inactividad -->
<div class="modal fade" id="idleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sesión por expirar</h5>
            </div>
            <div class="modal-body">
                Tu sesión está a punto de cerrarse por inactividad. ¿Deseas continuar?
                <div id="countdown" class="fs-4 fw-bold text-center mt-3">60</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="keepAlive()">Continuar sesión</button>
            </div>
        </div>
    </div>
</div>
