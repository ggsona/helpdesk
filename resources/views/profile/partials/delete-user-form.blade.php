<section>
    <header class="mb-4">
        <h5 class="fw-bold text-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Eliminar Cuenta
        </h5>
        <p class="text-muted small">
            Una vez que tu cuenta sea eliminada, todos sus recursos y datos se borrarán permanentemente.
        </p>
    </header>

    <div class="p-3 bg-danger-subtle bg-opacity-10 border border-danger-subtle rounded-3">
        <p class="text-muted small mb-3">
            Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
        </p>
        <button class="btn btn-danger fw-bold px-4" 
                data-bs-toggle="modal" 
                data-bs-target="#confirmUserDeletion">
            {{ __('Eliminar mi cuenta') }}
        </button>
    </div>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; background: var(--bs-tertiary-bg);">
                <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
                    @csrf
                    @method('delete')

                    <h5 class="fw-bold mb-3">¿Estás seguro de que quieres eliminar tu cuenta?</h5>
                    <p class="text-muted small mb-4">
                        Por favor, introduce tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.
                    </p>

                    <div class="mb-4">
                        <label class="form-label-custom" for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control form-control-premium" placeholder="Introduce tu contraseña">
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">
                            Eliminar Cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>