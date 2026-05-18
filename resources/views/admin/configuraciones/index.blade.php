@extends('layouts.admin')

@section('content')
<style>
    /* Estilos Premium para el Arrastre y Soltado (Drag & Drop) */
    .sortable-ghost {
        opacity: 0.35 !important;
        border: 2px dashed rgba(37, 99, 235, 0.5) !important;
        background-color: rgba(37, 99, 235, 0.05) !important;
        box-shadow: none !important;
        transform: scale(0.98);
    }
    .sortable-drag {
        opacity: 0.9 !important;
        transform: scale(1.03);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25) !important;
        cursor: grabbing !important;
    }
    .cursor-grab:active {
        cursor: grabbing !important;
    }
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 theme-text">
                <i class="bi bi-gear-fill text-secondary me-2"></i> Ajustes de Sistema
            </h2>
            <p class="text-secondary mb-0">Configura reglas globales de la plataforma y niveles de organización.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- CONFIGURACIÓN DE NIVELES (DRAG & DROP) --}}
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold theme-text mb-1"><i class="bi bi-layers-fill text-accent me-2"></i> Nomenclatura Estructural</h5>
                        <p class="small text-muted mb-0">Define la jerarquía de mayor a menor para tu institución.</p>
                    </div>
                    <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalNuevoNivel">
                        <i class="bi bi-plus-lg"></i> Añadir
                    </button>
                </div>
                <div class="card-body">
                    
                    @if($existenUnidades)
                        <div class="alert alert-warning border-warning-subtle bg-warning-subtle text-warning-emphasis p-3 rounded-3 mb-4 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lock-fill fs-4 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Estructura Bloqueada</h6>
                                    <p class="mb-0 small" style="line-height: 1.2;">No puedes reordenar los niveles porque ya existen departamentos creados. Para cambiar el orden, debes eliminar todo el organigrama primero.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info border-info-subtle bg-info-subtle text-info-emphasis p-3 rounded-3 mb-4 shadow-sm">
                            <p class="mb-0 small"><i class="bi bi-info-circle-fill me-1"></i> Arrastra y suelta para reordenar. El nivel 1 es la entidad más grande (Ej. Sede).</p>
                        </div>
                    @endif

                    <div class="row g-3 {{ $existenUnidades ? '' : 'sortable-list' }}" id="niveles-list">
                        @foreach($niveles as $nivel)
                            <div class="col-12 col-md-6 col-lg-4" data-id="{{ $nivel->id }}">
                                <div class="card border-0 rounded-4 shadow-sm bg-body h-100 hover-shadow transition-all" style="transition: all 0.2s ease; border: 1px solid rgba(0,0,0,0.05) !important;">
                                    <div class="card-body p-3.5 d-flex flex-column justify-content-between" style="min-height: 120px;">
                                        <!-- Fila Superior: Nivel y Switch -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                                                Posición {{ $nivel->nivel }}
                                            </span>
                                            <div class="form-check form-switch mb-0 flex-shrink-0">
                                                <input class="form-check-input toggle-nivel-btn cursor-pointer m-0" type="checkbox" role="switch" data-id="{{ $nivel->id }}" {{ $nivel->is_active ? 'checked' : '' }} {{ $existenUnidades ? 'disabled' : '' }} style="width: 2.5rem; height: 1.25rem;">
                                            </div>
                                        </div>
                                        
                                        <!-- Fila Inferior: Handle de arrastre y Nombre -->
                                        <div class="d-flex align-items-center mt-auto">
                                            <i class="bi bi-grid-3x3-gap-fill text-muted me-3 {{ $existenUnidades ? 'opacity-25' : 'cursor-grab handle' }} fs-4" style="{{ $existenUnidades ? '' : 'cursor: grab;' }}"></i>
                                            <span class="fw-bold theme-text fs-5" style="word-break: break-word; line-height: 1.3;">{{ $nivel->nombre }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- PROXIMAS CONFIGURACIONES --}}
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="fw-bold theme-text mb-1"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Active Directory / Login</h5>
                    <p class="small text-muted mb-0">Configuraciones de acceso a la plataforma.</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center py-5">
                    <div class="text-center opacity-50">
                        <i class="bi bi-cone-striped fs-1 mb-3 d-block text-warning"></i>
                        <h5 class="fw-bold theme-text">Próximamente</h5>
                        <p class="small mb-0">Esta área servirá para configurar el registro y Active Directory.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVO NIVEL (Nomenclatura) --}}
<div class="modal fade" id="modalNuevoNivel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content card-premium border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold theme-text"><i class="bi bi-tag-fill text-primary me-2"></i> Nuevo Nivel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.configuraciones.niveles.store') }}" method="POST">
                @csrf
                <div class="modal-body pb-0">
                    <p class="small text-muted mb-3">Se agregará al final de la jerarquía. Podrás arrastrarlo luego.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nombre del Nivel</label>
                        <input type="text" name="nombre" class="form-control form-control-premium" required placeholder="Ej. Bloque, Zona">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold shadow-sm">Agregar Catálogo</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts para AJAX --}}
@if(!$existenUnidades)
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('niveles-list');
    var sortable = Sortable.create(el, {
        handle: '.handle', 
        animation: 200,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function (evt) {
            // 1. Recalcular y actualizar visualmente las etiquetas "Posición X" instantáneamente
            let index = 1;
            document.querySelectorAll('#niveles-list > div').forEach(function(item) {
                let badge = item.querySelector('.badge');
                if (badge) {
                    badge.innerHTML = 'Posición ' + index;
                }
                index++;
            });

            // 2. Extraer el nuevo orden de los IDs
            let ordenIds = [];
            document.querySelectorAll('#niveles-list > div').forEach(function(item) {
                ordenIds.push(item.getAttribute('data-id'));
            });

            // 3. Guardar en la base de datos de forma silenciosa por AJAX (Sin recargar!)
            fetch("{{ route('admin.configuraciones.niveles.reorder') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ orden: ordenIds })
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) {
                    alert('Error al guardar el orden: ' + data.message);
                }
            })
            .catch(error => {
                console.error("Error reordenando niveles:", error);
                alert("Hubo un error de red al intentar guardar la posición.");
            });
        }
    });
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Escuchar cambios en los switches
    document.querySelectorAll('.toggle-nivel-btn').forEach(switchBtn => {
        switchBtn.addEventListener('change', function() {
            let idNivel = this.getAttribute('data-id');
            let isChecked = this.checked;

            fetch(`/admin/configuraciones/niveles/${idNivel}/toggle`, {
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
                console.error("Error al activar/desactivar nivel:", error);
                this.checked = !isChecked;
            });
        });
    });
});
</script>
@endsection
