@php
    $layout = auth()->user()->hasRole('admin') ? 'admin-layout' : (auth()->user()->hasRole('tecnico') ? 'admin-layout' : 'cliente-layout');
@endphp

<x-dynamic-component :component="$layout">
    <div class="py-4">
        <div class="mb-4">
            <h2 class="fw-bold theme-text">Configuración del Perfil</h2>
            <p class="text-muted">Gestiona la información de tu cuenta y la seguridad.</p>
        </div>

        <div class="space-y-6">
            <div class="p-4 sm:p-8 card-premium shadow-sm">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 card-premium shadow-sm mt-4">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 card-premium shadow-sm mt-4">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>