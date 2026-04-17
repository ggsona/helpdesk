<section>
    <header class="mb-4">
        <h5 class="fw-bold">
            <i class="bi bi-person-badge me-2 text-primary"></i>
            Información del Perfil
        </h5>
        <p class="text-muted small">
            Actualiza la información de tu cuenta y la dirección de correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label class="form-label-custom" for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control form-control-premium" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-4">
            <label class="form-label-custom" for="email">Correo Electrónico</label>
            <input type="email" name="email" id="email" class="form-control form-control-premium" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-warning-subtle rounded-3">
                    <p class="text-sm text-warning-emphasis mb-2">
                        Tu dirección de correo no está verificada.
                    </p>
                    <button form="send-verification" class="btn btn-sm btn-warning fw-bold">
                        Re-enviar correo de verificación
                    </button>
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                {{ __('Guardar Cambios') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-success small mb-0 animate__animated animate__fadeIn">
                    <i class="bi bi-check-lg"></i> {{ __('Guardado.') }}
                </p>
            @endif
        </div>
    </form>
</section>