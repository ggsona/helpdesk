@extends('layouts.admin')

@section('content')
<style>
    .hover-scale {
        transition: all 0.2s ease !important;
    }
    .hover-scale:hover {
        transform: scale(1.04);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Estilos premium para los Switches de Activo/Inactivo */
    .switch-premium {
        width: 2.8rem;
        height: 1.5rem;
        cursor: pointer;
    }
</style>

<div class="container-fluid py-4">
    
    <!-- Cabecera de la Página -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 theme-text">
                <i class="bi bi-people-fill text-primary me-2"></i> Gestión de Usuarios
            </h2>
            <p class="text-secondary mb-0">Administra todos los usuarios registrados, cambia sus roles operativos o desactiva sus accesos de forma temporal.</p>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold p-3 rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger bg-opacity-10 text-danger fw-bold p-3 rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Contenedor Principal de la Tabla -->
    <div class="card card-premium shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h5 class="fw-bold theme-text mb-0">Directorio de Usuarios</h5>
            
            <!-- Buscador en tiempo real -->
            <div class="input-group shadow-sm rounded-pill overflow-hidden border border-secondary border-opacity-25" style="max-width: 320px;">
                <span class="input-group-text border-0 text-secondary" style="background-color: var(--bs-body-bg);"><i class="bi bi-search"></i></span>
                <input type="text" id="user-search-input" class="form-control border-0 placeholder-secondary small" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);" placeholder="Buscar por nombre, correo o cédula...">
            </div>
        </div>
        
        <div class="card-body p-4">
            @if($usuarios->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-hover border-0">
                        <thead>
                            <tr class="text-secondary border-bottom border-secondary border-opacity-25" style="font-size: 0.85rem; font-weight: 600;">
                                <th class="pb-3" style="width: 25%;">Usuario / Contacto</th>
                                <th class="pb-3" style="width: 15%;">Cédula</th>
                                <th class="pb-3" style="width: 30%;">Ubicación Organizacional</th>
                                <th class="pb-3" style="width: 15%;">Rol Asignado</th>
                                <th class="pb-3 text-center" style="width: 15%;">Acceso (Activo)</th>
                            </tr>
                        </thead>
                        <tbody id="usuarios-table-body">
                            @foreach($usuarios as $user)
                                @php
                                    $unidad = $user->persona->unidadAdministrativa ?? null;
                                    $path = [];
                                    while($unidad) {
                                        $path[] = $unidad->nombre;
                                        $unidad = $unidad->parent;
                                    }
                                    $pathReverse = array_reverse($path);
                                    
                                    $pathHtml = '';
                                    foreach($pathReverse as $index => $node) {
                                        $pathHtml .= '<span class="badge bg-secondary bg-opacity-10 border border-secondary border-opacity-25 text-body fw-medium px-2 py-1" style="font-size: 0.75rem; letter-spacing: 0.3px;">' . $node . '</span>';
                                        if ($index < count($pathReverse) - 1) {
                                            $pathHtml .= '<i class="bi bi-chevron-right text-secondary opacity-50 small" style="font-size: 0.65rem;"></i>';
                                        }
                                    }
                                    
                                    // Determinar badge de rol
                                    $rolName = $user->roles->first()->name ?? 'usuario';
                                    $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary';
                                    if ($rolName === 'admin') $badgeClass = 'bg-danger bg-opacity-10 text-danger border-danger';
                                    elseif ($rolName === 'gestor') $badgeClass = 'bg-warning bg-opacity-10 text-warning border-warning';
                                    elseif ($rolName === 'tecnico') $badgeClass = 'bg-info bg-opacity-10 text-info border-info';
                                @endphp
                                <tr class="border-bottom border-secondary border-opacity-10" style="transition: all 0.2s;">
                                    <!-- Datos Personales -->
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2.5 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold theme-text d-block user-name">{{ $user->name }}</span>
                                                <span class="text-muted small d-block user-email" style="font-size: 0.75rem;"><i class="bi bi-envelope-fill me-1"></i> {{ $user->email }}</span>
                                                <span class="text-muted small d-block" style="font-size: 0.75rem;"><i class="bi bi-telephone-fill me-1"></i> {{ $user->persona->telefono ?? 'Sin teléfono' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Cédula -->
                                    <td class="py-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold user-cedula" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                            <i class="bi bi-card-text me-1 opacity-75"></i> {{ $user->persona->cedula ?? 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Ubicación Jerárquica -->
                                    <td class="py-3">
                                        @if(!empty($pathHtml))
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                {!! $pathHtml !!}
                                            </div>
                                        @else
                                            <span class="text-warning small fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> No especificada</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Rol y Editar Rol -->
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $badgeClass }} border border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                {{ strtoupper($rolName) }}
                                            </span>
                                            @if($user->id !== auth()->id())
                                                <button class="btn btn-sm btn-primary bg-opacity-10 text-primary border-0 rounded-circle p-1 hover-scale d-flex align-items-center justify-content-center shadow-sm" 
                                                        style="width: 28px; height: 28px; background-color: rgba(13, 110, 253, 0.1);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editRoleModal-{{ $user->id }}" 
                                                        title="Editar Rol">
                                                    <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Switch de Aprobación (Activo / Inactivo) -->
                                    <td class="py-3 text-center align-middle">
                                        <div class="form-check form-switch d-flex justify-content-center m-0 fs-5">
                                            <input class="form-check-input hover-scale toggle-user-status" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   data-id="{{ $user->id }}"
                                                   {{ $user->is_approved ? 'checked' : '' }}
                                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                                   title="{{ $user->id === auth()->id() ? 'No puedes desactivar tu propia cuenta' : 'Activar / Desactivar cuenta' }}">
                                        </div>
                                    </td>
                                </tr>

                                {{-- MODAL DE EDICIÓN DE ROL PARA ESTE USUARIO --}}
                                @if($user->id !== auth()->id())
                                    <div class="modal fade" id="editRoleModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content card-premium border-0">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold theme-text"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Cambiar Rol</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.usuarios.update-role', $user->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body pb-0">
                                                        <p class="small text-muted mb-3">Selecciona el nuevo rol operativo para <strong>{{ $user->name }}</strong>.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label text-secondary small fw-semibold">Rol del Sistema</label>
                                                            <select name="role_name" class="form-select border-secondary border-opacity-25 rounded-3 fw-bold" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                                                                @foreach($roles as $role)
                                                                    <option value="{{ $role->name }}" {{ $rolName === $role->name ? 'selected' : '' }}>
                                                                        {{ strtoupper($role->name) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-2">
                                                        <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold shadow-sm hover-scale">Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-primary bg-opacity-5 p-4 rounded-circle d-inline-flex mb-4">
                        <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold theme-text mb-2">No hay usuarios registrados</h5>
                    <p class="text-muted mx-auto mb-0" style="max-width: 400px;">
                        Actualmente no existen usuarios en el sistema, lo cual es inusual. Revisa los seeders de tu base de datos.
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Script para Filtro de Búsqueda y Toggle Asíncrono --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Buscador Dinámico en tiempo real
    const searchInput = document.getElementById('user-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let query = this.value.toLowerCase().trim();
            document.querySelectorAll('#usuarios-table-body tr').forEach(row => {
                let name = row.querySelector('.user-name').textContent.toLowerCase();
                let email = row.querySelector('.user-email').textContent.toLowerCase();
                let cedula = row.querySelector('.user-cedula').textContent.toLowerCase();
                
                if (name.includes(query) || email.includes(query) || cedula.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 2. Toggle Asíncrono del Estado de Aprobación
    document.querySelectorAll('.toggle-user-status').forEach(switchInput => {
        switchInput.addEventListener('change', function() {
            let userId = this.getAttribute('data-id');
            let isChecked = this.checked;

            fetch(`/admin/usuarios/${userId}/toggle`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) {
                    alert(data.message);
                    this.checked = !isChecked; // Revertir visualmente si falló
                }
            })
            .catch(error => {
                console.error("Error al activar/desactivar usuario:", error);
                alert("Hubo un error de red al intentar cambiar el acceso del usuario.");
                this.checked = !isChecked; // Revertir visualmente si falló
            });
        });
    });
});
</script>
@endsection
