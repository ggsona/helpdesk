@extends('layouts.admin')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.roles.index') }}" class="text-decoration-none small text-muted hover-primary">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista de roles
        </a>
        <h2 class="fw-bold theme-text mt-3"><i class="bi bi-shield-shaded me-2 text-warning"></i>Editar Rol: <span class="text-capitalize text-primary">{{ $role->name }}</span></h2>
        <p class="text-muted">Ajusta los accesos y sincroniza los permisos asignados a este rol operativo.</p>
    </div>

    @php
        $rolesProtegidos = ['admin', 'gestor', 'tecnico', 'usuario'];
        $esProtegido = in_array($role->name, $rolesProtegidos);
    @endphp

    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card-premium shadow-sm p-4 mb-5 border-0">
                
                @if($esProtegido)
                    <div class="alert alert-warning border-warning-subtle bg-warning-subtle text-warning-emphasis d-flex align-items-center rounded-3 p-3 mb-4 shadow-sm" role="alert">
                        <i class="bi bi-shield-fill-exclamation fs-4 me-3"></i>
                        <div>
                            <strong class="d-block">Rol de Sistema Protegido</strong>
                            El nombre de este rol es vital para la lógica interna y no puede ser modificado. Sin embargo, eres libre de agregar o quitar permisos según tus necesidades de seguridad.
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- Nombre del Rol --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold theme-text" for="name">Nombre del Rol</label>
                        <input type="text" name="name" id="name" 
                               class="form-control form-control-premium @error('name') is-invalid @enderror" 
                               placeholder="Ej: tecnico-junior, gestor-redes..." 
                               value="{{ old('name', $role->name) }}"
                               {{ $esProtegido ? 'readonly' : '' }} required>
                        @if(!$esProtegido)
                            <small class="text-muted d-block mt-1">El nombre se convertirá a minúsculas automáticamente en el sistema.</small>
                        @endif
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4 opacity-25">

                    {{-- Grilla de Permisos --}}
                    <h5 class="fw-bold theme-text mb-3"><i class="bi bi-check2-all text-primary me-2"></i>Sincronizar Permisos</h5>
                    <p class="text-muted small mb-4">Selecciona los accesos y acciones operativas autorizadas para este rol:</p>
                    
                    <div class="row g-3">
                        @foreach($permissions as $permiso)
                            @php
                                $color = 'primary';
                                $icon = 'bi-shield-check';
                                $desc = 'Permiso general del sistema.';
                                
                                if(str_contains($permiso->name, 'ticket')) {
                                    if(str_contains($permiso->name, 'crear')) {
                                        $color = 'success'; $icon = 'bi-plus-circle-fill';
                                        $desc = 'Permite crear solicitudes de soporte básicas y borradores.';
                                    } elseif(str_contains($permiso->name, 'asignar')) {
                                        $color = 'warning'; $icon = 'bi-person-fill-gear';
                                        $desc = 'Permite asignar especialistas y reasignar casos.';
                                    } elseif(str_contains($permiso->name, 'resolver')) {
                                        $color = 'info'; $icon = 'bi-check-circle-fill';
                                        $desc = 'Permite atender y reportar la resolución de tickets.';
                                    }
                                } elseif(str_contains($permiso->name, 'interno')) {
                                    $color = 'danger'; $icon = 'bi-chat-left-dots-fill';
                                    $desc = 'Permite enviar y ver notas privadas exclusivas para el staff.';
                                } elseif(str_contains($permiso->name, 'roles')) {
                                    $color = 'dark'; $icon = 'bi-gear-fill';
                                    $desc = 'Permiso total para administrar roles, permisos y accesos globales.';
                                } elseif(str_contains($permiso->name, 'panel')) {
                                    $color = 'secondary'; $icon = 'bi-layout-text-sidebar-reverse';
                                    $desc = 'Permite el acceso al panel operativo y bandeja del staff.';
                                } elseif(str_contains($permiso->name, 'usuarios')) {
                                    $color = 'primary'; $icon = 'bi-people-fill';
                                    $desc = 'Permite gestionar el directorio de usuarios, asignar accesos y aprobar nuevos registros.';
                                } elseif(str_contains($permiso->name, 'configuraciones')) {
                                    $color = 'primary'; $icon = 'bi-sliders';
                                    $desc = 'Permite configurar la estructura organizacional y jerarquías del sistema.';
                                }
                            @endphp
                            <div class="col-md-6">
                                <div class="card p-3 shadow-sm border border-secondary border-opacity-10 h-100 theme-bg-dark" style="border-radius: 12px;">
                                    <div class="form-check d-flex align-items-start">
                                        <input class="form-check-input mt-1 me-2 shadow-none border-secondary" type="checkbox" name="permissions[]" 
                                               value="{{ $permiso->id }}" id="perm_{{ $permiso->id }}"
                                               {{ in_array($permiso->id, $rolePermissions) ? 'checked' : '' }}>
                                        
                                        <div class="ms-1">
                                            <label class="form-check-label fw-bold theme-text text-capitalize" for="perm_{{ $permiso->id }}">
                                                <i class="bi {{ $icon }} text-{{ $color }} me-1"></i> {{ str_replace('-', ' ', $permiso->name) }}
                                            </label>
                                            <p class="text-muted small mb-0 mt-1" style="font-size: 0.75rem; line-height: 1.4;">
                                                {{ $desc }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-5">
                        <hr class="my-4 opacity-25">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                        <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow text-white">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
