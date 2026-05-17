@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold theme-text mb-1"><i class="bi bi-shield-lock-fill me-2 text-primary"></i>Roles y Permisos</h2>
            <p class="text-muted mb-0">Administra las credenciales y define el alcance operativo del personal de soporte.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Rol
        </a>
    </div>

    {{-- Notificaciones del Sistema --}}
    @if(session('success'))
        <div class="alert alert-success border-success-subtle bg-success-subtle text-success-emphasis alert-dismissible fade show rounded-3 p-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger-emphasis alert-dismissible fade show rounded-3 p-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabla Premium de Roles --}}
    <div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: var(--bg-main);">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-muted small text-uppercase" style="width: 250px;">Nombre del Rol</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Permisos Asignados</th>
                        <th class="py-3 border-0 text-end pe-4 text-muted small text-uppercase" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        @php
                            $rolesProtegidos = ['admin', 'gestor', 'tecnico', 'usuario'];
                            $esProtegido = in_array($role->name, $rolesProtegidos);
                        @endphp
                        <tr style="border-bottom: 1px solid var(--bs-border-color);">
                            {{-- Nombre del Rol --}}
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 42px; height: 42px;">
                                        <i class="bi bi-person-badge fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold theme-text text-capitalize d-block">{{ $role->name }}</span>
                                        @if($esProtegido)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle small" style="font-size: 0.65rem;">SISTEMA BASE</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small" style="font-size: 0.65rem;">DINÁMICO</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Permisos vinculados --}}
                            <td class="py-3">
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($role->permissions as $permiso)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 text-capitalize" style="font-size: 0.75rem; font-weight: 500; border-radius: 8px;">
                                            <i class="bi bi-check-lg me-1"></i> {{ str_replace('-', ' ', $permiso->name) }}
                                        </span>
                                    @empty
                                        <span class="text-muted fst-italic small"><i class="bi bi-lock-fill me-1"></i> Sin permisos asignados (Acceso Restringido)</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Acciones CRUD --}}
                            <td class="py-3 text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                       class="btn btn-sm btn-outline-warning border-warning-subtle px-3 rounded-3 d-flex align-items-center shadow-sm"
                                       title="Editar permisos del rol">
                                        <i class="bi bi-pencil-square me-2"></i> Editar
                                    </a>
                                    
                                    @if(!$esProtegido)
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente este rol? Los usuarios con este rol perderán todos sus privilegios.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-danger-subtle px-2 rounded-3 shadow-sm"
                                                    title="Eliminar Rol">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="opacity-25 mb-3">
                                    <i class="bi bi-shield-slash display-1 theme-text"></i>
                                </div>
                                <p class="text-muted fst-italic">No hay roles registrados en el sistema.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
