@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 theme-text">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i> Gestión del Organigrama
            </h2>
            <p class="text-secondary mb-0">Administra las áreas físicas, dependencias y sucursales de la institución.</p>
        </div>
        <button class="btn btn-primary rounded-3 px-4 shadow-sm d-inline-flex align-items-center gap-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevaUnidad">
            <i class="bi bi-plus-lg"></i> Registrar Nueva Unidad
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger bg-opacity-10 text-danger fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-premium shadow-sm border-0">
        <div class="card-body p-4">
            
            @if($unidadesRaiz->isEmpty())
                <div class="text-center py-5 opacity-50">
                    <i class="bi bi-building-add display-1 text-primary mb-3 d-block"></i>
                    <h4 class="fw-bold theme-text">Organigrama Vacío</h4>
                    <p>No has registrado ninguna unidad principal. Comienza agregando tu primera Sede o Departamento.</p>
                </div>
            @else
                <div class="accordion accordion-flush" id="organigramaAccordion">
                    {{-- Usaremos un include recursivo o iteración para renderizar el árbol --}}
                    @foreach($unidadesRaiz as $raiz)
                        @include('admin.estructura.partials.unidad_item', ['unidad' => $raiz, 'parentId' => 'organigramaAccordion'])
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL NUEVA UNIDAD --}}
<div class="modal fade" id="modalNuevaUnidad" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-premium border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold theme-text"><i class="bi bi-node-plus-fill text-primary me-2"></i> Añadir Unidad Operativa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.estructura.unidades.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nombre (Ej. Tecnología, Redes)</label>
                        <input type="text" name="nombre" class="form-control form-control-premium" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nomenclatura Jerárquica</label>
                        <select name="id_nivel" class="form-select form-select-premium" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($niveles as $nivel)
                                @if($nivel->is_active)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">¿Depende de otra unidad?</label>
                        <select name="parent_id" class="form-select form-select-premium">
                            <option value="">Ninguna (Es una raíz principal)</option>
                            {{-- Recursión plana para el dropdown --}}
                            @php
                                $printDropdown = function($unidades, $prefix = '') use (&$printDropdown) {
                                    foreach($unidades as $u) {
                                        echo '<option value="'.$u->id.'">'.$prefix.$u->nombre.'</option>';
                                        if($u->children->count() > 0) {
                                            $printDropdown($u->children, $prefix . '— ');
                                        }
                                    }
                                };
                            @endphp
                            {{ $printDropdown($unidadesRaiz) }}
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
