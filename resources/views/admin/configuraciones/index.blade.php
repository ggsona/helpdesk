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
        {{-- CONFIGURACIÓN DE SEGURIDAD DE SESIÓN --}}
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="fw-bold theme-text mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i> Seguridad de Sesión</h5>
                    <p class="small text-muted mb-0">Configura el tiempo máximo de inactividad antes de cerrar la sesión automáticamente.</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.configuraciones.sesion.update') }}" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold text-secondary">Tiempo de Inactividad</label>
                                <div class="input-group">
                                    <input type="number" name="sesion_timeout" class="form-control form-control-premium" value="{{ \App\Models\Configuracion::where('clave', 'sesion_timeout')->value('valor') ?? 30 }}" required min="1">
                                    <select name="sesion_unit" class="form-select bg-white fw-bold" style="max-width: 120px; background-color: var(--card-bg) !important; color: var(--text-main) !important; border: 1px solid var(--border-color) !important;">
                                        <option value="minutos">Minutos</option>
                                        <option value="horas">Horas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mt-md-0 pt-md-2">
                                <button type="submit" class="btn btn-warning px-4 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-save-fill me-2"></i>Guardar Configuración
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CONFIGURACIÓN DE LIMITES DEL SISTEMA --}}
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold theme-text mb-1"><i class="bi bi-hdd-fill text-primary me-2"></i> Límites de Almacenamiento Global</h5>
                        <p class="small text-muted mb-0">Configura el tamaño máximo permitido para subir archivos a la base de conocimiento y tickets.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.configuraciones.limites.update') }}" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold text-secondary">Tamaño Máximo de Adjuntos</label>
                                @php
                                    $kbActual = env('KB_MAX_UPLOAD_KB', 1048576);
                                    // Si es divisible exactamente por 1048576 (1GB), lo mostramos en GB, sino en MB
                                    $esGb = ($kbActual >= 1048576 && $kbActual % 1048576 == 0);
                                    $valorMostrar = $esGb ? ($kbActual / 1048576) : ($kbActual / 1024);
                                @endphp
                                <div class="input-group">
                                    <input type="number" name="upload_size_value" class="form-control form-control-premium" value="{{ $valorMostrar }}" required min="1">
                                    <select name="upload_size_unit" class="form-select fw-bold" style="max-width: 100px; background-color: var(--card-bg) !important; color: var(--text-main) !important; border: 1px solid var(--border-color) !important;">
                                        <option value="MB" {{ !$esGb ? 'selected' : '' }}>MB</option>
                                        <option value="GB" {{ $esGb ? 'selected' : '' }}>GB</option>
                                    </select>
                                </div>
                                <div class="form-text small opacity-75">Ejemplo: 500 MB o 1 GB. Afecta a bases de conocimiento y tickets.</div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mt-md-0 pt-md-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-save-fill me-2"></i>Guardar Límite
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                                                Posición {{ $nivel->nivel }}
                                            </span>
                                            <div class="form-check form-switch mb-0 p-0 flex-shrink-0">
                                                <input class="form-check-input toggle-nivel-btn cursor-pointer" type="checkbox" role="switch" data-id="{{ $nivel->id }}" {{ $nivel->is_active ? 'checked' : '' }} {{ $existenUnidades ? 'disabled' : '' }} style="width: 2.5rem; height: 1.25rem; margin-left: 0; float: none;">
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

        {{-- CONFIGURACIÓN DE ACTIVE DIRECTORY / LDAP --}}
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold theme-text mb-1"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Integración de Identidad (AD / LDAP)</h5>
                        <p class="small text-muted mb-0">Permite validar cuentas e información de usuarios contra un directorio centralizado corporativo.</p>
                    </div>
                    <div class="form-check form-switch mb-0 p-0 flex-shrink-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="ad_enabled" id="ad_enabled" {{ config('ad.enabled') ? 'checked' : '' }} style="width: 2.8rem; height: 1.4rem; margin-left: 0; float: none;">
                    </div>
                </div>

                <!-- Selector de Proveedor LDAP en Tabs -->
                <div class="px-4 mt-3">
                    <ul class="nav nav-pills gap-2" id="ldap-provider-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ config('ad.provider', 'activedirectory') == 'activedirectory' ? 'active' : '' }} btn-sm rounded-pill px-3 py-1.5 fw-semibold" id="provider-ad-tab" data-bs-toggle="pill" type="button" role="tab" onclick="switchLdapProvider('activedirectory')">
                                <i class="bi bi-windows me-2"></i>Windows Active Directory
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ config('ad.provider') == 'openldap' ? 'active' : '' }} btn-sm rounded-pill px-3 py-1.5 fw-semibold" id="provider-openldap-tab" data-bs-toggle="pill" type="button" role="tab" onclick="switchLdapProvider('openldap')">
                                <i class="bi bi-ubuntu me-2"></i>OpenLDAP / Generic LDAP
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.configuraciones.ad.update') }}" method="POST" id="ldap-config-form">
                        @csrf
                        
                        <!-- Proveedor Oculto -->
                        <input type="hidden" name="ad_provider" id="ad_provider" value="{{ config('ad.provider', 'activedirectory') }}">

                        <!-- Panel informativo -->
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info p-3 rounded-4 mb-4 shadow-sm">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill fs-5 me-3 mt-0.5"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" id="info-title">¿Cómo funciona esta integración?</h6>
                                    <p class="mb-0 small" id="info-desc" style="line-height: 1.4;">
                                        Al habilitarse, los nuevos usuarios que se registren deberán ingresar su usuario de Active Directory. 
                                        El sistema verificará si el usuario existe en el dominio de Windows Server antes de permitir su aprobación.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Sección: Servidor -->
                            <div class="col-12">
                                <h6 class="fw-bold theme-text mb-2 text-primary opacity-75">
                                    <i class="bi bi-server me-2"></i>Servidor y Conexión
                                </h6>
                                <hr class="my-2 opacity-10">
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small fw-bold text-secondary" id="label-host">Domain Controller (Host / IP)</label>
                                <input type="text" name="ad_host" id="ad_host" class="form-control form-control-premium" value="{{ config('ad.host') }}" placeholder="ldap://dominio.local o IP">
                                <div class="form-text small opacity-75" id="help-host">Ejemplo: ldap://192.168.1.100 o ldaps://domain.local</div>
                            </div>

                            <div class="col-12 col-md-3 col-lg-2">
                                <label class="form-label small fw-bold text-secondary">Puerto</label>
                                <input type="number" name="ad_port" class="form-control form-control-premium" value="{{ config('ad.port') }}" placeholder="389">
                                <div class="form-text small opacity-75">LDAP estándar: 389. Seguro (SSL): 636</div>
                            </div>

                            <div class="col-12 col-md-3 col-lg-3">
                                <label class="form-label small fw-bold text-secondary">Cifrado</label>
                                <select name="ad_encryption" class="form-select form-select-premium">
                                    <option value="none" {{ config('ad.encryption') == 'none' ? 'selected' : '' }}>Ninguno</option>
                                    <option value="ssl" {{ config('ad.encryption') == 'ssl' ? 'selected' : '' }}>SSL (LDAPS)</option>
                                    <option value="tls" {{ config('ad.encryption') == 'tls' ? 'selected' : '' }}>STARTTLS</option>
                                </select>
                                <div class="form-text small opacity-75">Canal de comunicación seguro</div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold text-secondary">Base DN</label>
                                <input type="text" name="ad_base_dn" id="ad_base_dn" class="form-control form-control-premium" value="{{ config('ad.base_dn') }}" placeholder="dc=dominio,dc=local">
                                <div class="form-text small opacity-75" id="help-basedn">Ejemplo: DC=empresa,DC=com</div>
                            </div>

                            <!-- Sección: Credenciales Bind -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold theme-text mb-2 text-primary opacity-75">
                                    <i class="bi bi-shield-lock me-2"></i>Cuenta de Servicio (Bind Account)
                                </h6>
                                <hr class="my-2 opacity-10">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary" id="label-binduser">Usuario Bind (DN completo o sAMAccountName)</label>
                                <input type="text" name="ad_user" id="ad_user" class="form-control form-control-premium" value="{{ config('ad.user') }}" placeholder="CN=Admin,CN=Users,DC=dominio,DC=local">
                                <div class="form-text small opacity-75">Cuenta con permisos de lectura en el directorio</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Contraseña Bind</label>
                                <input type="password" name="ad_password" class="form-control form-control-premium" placeholder="••••••••">
                                <div class="form-text small opacity-75">Deje vacío si desea conservar la contraseña configurada</div>
                            </div>

                            <!-- Sección: Atributos y Filtros -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold theme-text mb-2 text-primary opacity-75">
                                    <i class="bi bi-sliders me-2"></i>Mapeo y Atributos de Búsqueda
                                </h6>
                                <hr class="my-2 opacity-10">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary">Atributo de Validación</label>
                                <select name="ad_attribute" id="ad_attribute" class="form-select form-select-premium">
                                    <!-- Se rellena dinámicamente con JS según el proveedor -->
                                </select>
                                <div class="form-text small opacity-75">Campo clave usado para validar</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary">Atributo de Nombre Completo</label>
                                <input type="text" name="ad_attr_name" id="ad_attr_name" class="form-control form-control-premium" value="{{ config('ad.attr_name', 'displayName') }}">
                                <div class="form-text small opacity-75" id="help-attrname">Ejemplo: displayName o cn</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary">Atributo de Correo Electrónico</label>
                                <input type="text" name="ad_attr_email" id="ad_attr_email" class="form-control form-control-premium" value="{{ config('ad.attr_email', 'mail') }}">
                                <div class="form-text small opacity-75">Ejemplo: mail</div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="mt-4 pt-2 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i>Guardar Ajustes de AD
                            </button>
                            <button type="button" class="btn btn-outline-secondary px-4 fw-bold rounded-pill shadow-sm" id="btn-probar-conexion">
                                <i class="bi bi-broadcast me-2"></i>Probar Conexión LDAP
                            </button>
                        </div>
                    </form>
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
// Función para alternar dinámicamente entre Active Directory y OpenLDAP
window.switchLdapProvider = function (provider) {
    const providerInput = document.getElementById('ad_provider');
    if (providerInput) {
        providerInput.value = provider;
    }

    const infoTitle = document.getElementById('info-title');
    const infoDesc = document.getElementById('info-desc');
    const labelHost = document.getElementById('label-host');
    const helpHost = document.getElementById('help-host');
    const helpBaseDn = document.getElementById('help-basedn');
    const labelBindUser = document.getElementById('label-binduser');
    const selectAttribute = document.getElementById('ad_attribute');
    const inputAttrName = document.getElementById('ad_attr_name');
    const helpAttrName = document.getElementById('help-attrname');

    if (!infoTitle) return; // Prevenir errores si se llama antes del DOM

    if (provider === 'activedirectory') {
        infoTitle.innerText = '¿Cómo funciona esta integración? (Windows Active Directory)';
        infoDesc.innerText = 'Al habilitarse, los nuevos usuarios que se registren deberán ingresar su usuario de Active Directory. El sistema verificará si el usuario existe en el dominio de Windows Server antes de permitir su aprobación.';
        
        labelHost.innerText = 'Domain Controller (Host / IP)';
        helpHost.innerText = 'Ejemplo: ldap://192.168.1.100 o ldaps://domain.local';
        helpBaseDn.innerText = 'Ejemplo: DC=empresa,DC=com';
        labelBindUser.innerText = 'Usuario Bind (DN completo o sAMAccountName)';
        
        selectAttribute.innerHTML = `
            <option value="samaccountname" ${"{{ config('ad.attribute', 'samaccountname') }}" === 'samaccountname' ? 'selected' : ''}>sAMAccountName (usuario)</option>
            <option value="userprincipalname" ${"{{ config('ad.attribute') }}" === 'userprincipalname' ? 'selected' : ''}>userPrincipalName (correo/UPN)</option>
        `;
        
        inputAttrName.placeholder = 'displayName';
        helpAttrName.innerText = 'Ejemplo: displayName o cn';
    } else if (provider === 'openldap') {
        infoTitle.innerText = '¿Cómo funciona esta integración? (OpenLDAP / Generic LDAP)';
        infoDesc.innerText = 'Al habilitarse, los nuevos usuarios que se registren deberán ingresar su identificador de OpenLDAP (uid). El sistema consultará el directorio Linux/OpenLDAP antes de permitir el registro.';
        
        labelHost.innerText = 'Servidor LDAP (Host / IP)';
        helpHost.innerText = 'Ejemplo: ldap://ldap.empresa.com o ldaps://192.168.1.200';
        helpBaseDn.innerText = 'Ejemplo: ou=usuarios,dc=empresa,dc=org';
        labelBindUser.innerText = 'Usuario Bind (DN completo, ej: cn=admin,...)';
        
        selectAttribute.innerHTML = `
            <option value="uid" ${"{{ config('ad.attribute') }}" === 'uid' ? 'selected' : ''}>uid (identificador único)</option>
            <option value="cn" ${"{{ config('ad.attribute') }}" === 'cn' ? 'selected' : ''}>cn (nombre común)</option>
        `;
        
        inputAttrName.placeholder = 'cn';
        helpAttrName.innerText = 'Ejemplo: cn o displayName';
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar el proveedor seleccionado
    switchLdapProvider("{{ config('ad.provider', 'activedirectory') }}");

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

    // Escuchar cambios en el switch de Active Directory
    const adSwitch = document.getElementById('ad_enabled');
    if (adSwitch) {
        adSwitch.addEventListener('change', function () {
            let isChecked = this.checked;
            fetch("{{ route('admin.configuraciones.ad.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ enabled: isChecked })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    this.checked = !isChecked;
                }
            })
            .catch(error => {
                console.error("Error al cambiar estado de AD:", error);
                this.checked = !isChecked;
            });
        });
    }

    // Probar conexión LDAP
    const btnTest = document.getElementById('btn-probar-conexion');
    if (btnTest) {
        btnTest.addEventListener('click', function () {
            const originalContent = btnTest.innerHTML;
            btnTest.disabled = true;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Conectando...';

            fetch("{{ route('admin.configuraciones.ad.test') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(response => response.json())
            .then(data => {
                btnTest.disabled = false;
                btnTest.innerHTML = originalContent;
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                btnTest.disabled = false;
                btnTest.innerHTML = originalContent;
                console.error("Error al probar conexión:", error);
                alert("Hubo un error de red al intentar conectar con el servidor Active Directory.");
            });
        });
    }
});
</script>
@endsection
