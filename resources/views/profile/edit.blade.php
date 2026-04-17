<x-admin-layout>
    <div class="mb-4">
        <h3 class="fw-bold">Configuración del Perfil</h3>
        <p class="text-muted">Gestiona la información de tu cuenta y la seguridad.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="section-card shadow-sm">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="section-card shadow-sm">
                @include('profile.partials.update-password-form')
            </div>

            <div class="section-card shadow-sm border-danger-subtle">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-admin-layout>