<div class="accordion-item bg-transparent border-0 mb-3 rounded-4 shadow-sm overflow-hidden" style="transition: all 0.3s ease;">
    <h2 class="accordion-header d-flex align-items-center bg-body border border-secondary border-opacity-10 rounded-4" id="heading-{{ $unidad->id }}">
        
        @if($unidad->children->count() > 0)
        <button class="accordion-button collapsed bg-transparent shadow-none w-auto pe-2 py-3 rounded-start-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $unidad->id }}">
        </button>
        @else
        <div class="ps-4 pe-2 py-3"></div> {{-- Espaciador si no tiene hijos --}}
        @endif

        <div class="flex-grow-1 d-flex align-items-center py-3 pe-4">
            <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-folder2-open text-warning fs-5"></i>
            </div>
            <div>
                <span class="fw-bold theme-text d-block fs-6 mb-1">{{ $unidad->nombre }}</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2" style="font-size: 0.7rem;">
                    {{ $unidad->nivel->nombre ?? 'Sin Nivel' }}
                </span>
            </div>
            
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-sm btn-light border-secondary border-opacity-25 text-secondary rounded-3" data-bs-toggle="modal" data-bs-target="#modalEditarUnidad{{ $unidad->id }}" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <form action="{{ route('admin.estructura.unidades.destroy', $unidad->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta unidad? ¡Atención! Los usuarios asignados a ella quedarán sin departamento hasta que se les reasigne.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-light border-secondary border-opacity-25 text-danger rounded-3" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </h2>
    
    @if($unidad->children->count() > 0)
    <div id="collapse-{{ $unidad->id }}" class="accordion-collapse collapse" data-bs-parent="#{{ $parentId }}">
        <div class="accordion-body border-top-0 bg-transparent pt-3 pb-0 ps-5 pe-0 ms-3 border-start border-2 border-secondary border-opacity-25">
            <div class="accordion accordion-flush" id="accordion-hijos-{{ $unidad->id }}">
                @foreach($unidad->children as $hijo)
                    @include('admin.estructura.partials.unidad_item', ['unidad' => $hijo, 'parentId' => 'accordion-hijos-' . $unidad->id])
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade" id="modalEditarUnidad{{ $unidad->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-premium border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold theme-text"><i class="bi bi-pencil-square text-primary me-2"></i> Editar Unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.estructura.unidades.update', $unidad->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nombre</label>
                        <input type="text" name="nombre" class="form-control form-control-premium" value="{{ $unidad->nombre }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nomenclatura Jerárquica</label>
                        <select name="id_nivel" class="form-select form-select-premium" required>
                            @foreach($niveles as $nivel)
                                @if($nivel->is_active || $nivel->id == $unidad->id_nivel)
                                    <option value="{{ $nivel->id }}" {{ $unidad->id_nivel == $nivel->id ? 'selected' : '' }}>{{ $nivel->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Depende de</label>
                        <select name="parent_id" class="form-select form-select-premium">
                            <option value="">Ninguna (Es una raíz principal)</option>
                            @php
                                $printDropdownEdit = function($unidades, $prefix = '') use (&$printDropdownEdit, $unidad) {
                                    foreach($unidades as $u) {
                                        if($u->id != $unidad->id) {
                                            $selected = ($unidad->parent_id == $u->id) ? 'selected' : '';
                                            echo '<option value="'.$u->id.'" '.$selected.'>'.$prefix.$u->nombre.'</option>';
                                            if($u->children->count() > 0) {
                                                $printDropdownEdit($u->children, $prefix . '— ');
                                            }
                                        }
                                    }
                                };
                            @endphp
                            {{ $printDropdownEdit($unidadesRaiz) }}
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
