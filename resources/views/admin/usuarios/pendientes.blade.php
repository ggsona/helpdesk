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
</style>

<div class="container-fluid py-4">
    
    <!-- Cabecera de la Página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 theme-text">
                <i class="bi bi-person-check-fill text-primary me-2"></i> Bandeja de Aprobaciones
            </h2>
            <p class="text-secondary mb-0">Revisa las solicitudes de nuevos registros, valida su departamento y asígnales un rol operativo.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold p-3 rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tarjetas de Resumen Rápido -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-0">Solicitudes Pendientes</h6>
                        <h3 class="fw-bold mb-0 theme-text">{{ $usuariosPendientes->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card card-premium shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold theme-text mb-0">Solicitudes de Registro Entrantes</h5>
        </div>
        
        <div class="card-body p-4">
            @if($usuariosPendientes->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-hover border-0">
                        <thead>
                            <tr class="text-secondary border-bottom border-secondary border-opacity-25" style="font-size: 0.85rem; font-weight: 600;">
                                <th class="pb-3" style="width: 25%;">Usuario / Contacto</th>
                                <th class="pb-3" style="width: 15%;">Cédula de Identidad</th>
                                <th class="pb-3" style="width: 35%;">Ubicación Organizacional</th>
                                <th class="pb-3 text-center" style="width: 25%;">Autorización y Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuariosPendientes as $user)
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
                                @endphp
                                <tr class="border-bottom border-secondary border-opacity-10" style="transition: all 0.2s;">
                                    <!-- Datos Básicos -->
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2.5 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold theme-text d-block">{{ $user->name }}</span>
                                                <span class="text-muted small d-block" style="font-size: 0.75rem;"><i class="bi bi-envelope-fill me-1"></i> {{ $user->email }}</span>
                                                <span class="text-muted small d-block" style="font-size: 0.75rem;"><i class="bi bi-telephone-fill me-1"></i> {{ $user->persona->telefono ?? 'Sin teléfono' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Cedula -->
                                    <td class="py-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                            <i class="bi bi-card-text me-1"></i> {{ $user->persona->cedula ?? 'N/A' }}
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
                                    <!-- Acciones de Aprobación -->
                                    <td class="py-3 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            
                                            <!-- Formulario de Aprobación -->
                                            <form action="{{ route('admin.usuarios.aprobar', $user->id) }}" method="POST" class="d-flex align-items-center gap-2 m-0">
                                                @csrf
                                                <!-- Combo de Selección de Rol -->
                                                <select name="role_name" class="form-select form-select-sm rounded-pill px-3 py-1.5 fw-bold border-secondary border-opacity-50" style="font-size: 0.8rem; min-width: 110px; cursor: pointer; color: var(--bs-body-color); background-color: var(--bs-body-bg);">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->name }}" {{ $role->name == 'usuario' ? 'selected' : '' }}>
                                                            {{ strtoupper($role->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center shadow-sm hover-scale border-opacity-50">
                                                    Aprobar <i class="bi bi-check-lg ms-1"></i>
                                                </button>
                                            </form>

                                            <!-- Botón de Rechazo -->
                                            <form action="{{ route('admin.usuarios.rechazar', $user->id) }}" method="POST" onsubmit="return confirm('¿Está completamente seguro de rechazar y borrar la solicitud de {{ $user->name }}? Esta acción no se puede deshacer.');" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center shadow-sm hover-scale border-opacity-50">
                                                    Rechazar <i class="bi bi-trash-fill ms-1"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Estado Vacío (Empty State) -->
                <div class="text-center py-5">
                    <div class="bg-primary bg-opacity-5 p-4 rounded-circle d-inline-flex mb-4">
                        <i class="bi bi-check2-all text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold theme-text mb-2">¡Todo al día por aquí!</h5>
                    <p class="text-muted mx-auto mb-0" style="max-width: 400px;">
                        No existen solicitudes de registro pendientes de aprobación en este momento. Las cuentas nuevas aparecerán aquí.
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
