<section>
    <header class="mb-4">
        <h5 class="fw-bold">
            <i class="bi bi-shield-lock me-2 text-primary"></i>
            Actualizar Contraseña
        </h5>
        <p class="text-muted small">
            Asegúrate de usar una contraseña larga y aleatoria para mantenerte seguro.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label-custom">Contraseña Actual</label>
            <input type="password" name="current_password" class="form-control form-control-premium" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="mb-3">
            <label class="form-label-custom">Nueva Contraseña</label>
            <input type="password" name="password" class="form-control form-control-premium" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="mb-4">
            <label class="form-label-custom">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="form-control form-control-premium" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                {{ __('Guardar Cambios') }}
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-success small mb-0 animate__animated animate__fadeIn">
                    <i class="bi bi-check-lg"></i> {{ __('Guardado.') }}
                </p>
            @endif
        </div>
    </form>
</section>