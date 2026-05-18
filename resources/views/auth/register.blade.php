<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Registro | {{ config('app.name', 'HelpDesk') }}</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0f111a;
            --bg-card: #151824;
            --primary: #2563eb;
            --primary-glow: rgba(37, 99, 235, 0.15);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }

        .split-container {
            min-height: 100vh;
        }

        /* Lado Izquierdo - Branding */
        .branding-side {
            background: linear-gradient(135deg, #090a0f 0%, #161b33 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
        }

        .branding-side::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            top: -10%;
            left: -10%;
            pointer-events: none;
        }

        .branding-side::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            bottom: -10%;
            right: -10%;
            pointer-events: none;
        }

        /* Lado Derecho - Formulario */
        .form-side {
            background-color: var(--bg-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
        }

        .form-card {
            background-color: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 650px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .form-control-premium {
            background-color: rgba(15, 17, 26, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.3s ease !important;
        }

        .form-control-premium:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px var(--primary-glow) !important;
        }

        .form-select-premium {
            background-color: rgba(15, 17, 26, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: var(--text-main) !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            transition: all 0.3s ease !important;
        }

        .form-select-premium:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px var(--primary-glow) !important;
        }

        .btn-premium {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }

        .theme-text-primary {
            color: #3b82f6;
        }

        /* Corrección de legibilidad para el Modo Oscuro */
        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .small.text-muted {
            color: rgba(255, 255, 255, 0.55) !important;
        }

        .form-label.text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .text-secondary {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Títulos de secciones en blanco brillante */
        .branding-side h6 {
            color: #ffffff !important;
        }

        .form-card h6 {
            color: #3b82f6 !important; /* Azul neón consistente para subtítulos */
        }

        /* Micro-animación interactiva para el enlace de retroceso */
        a.text-muted {
            color: rgba(255, 255, 255, 0.5) !important;
            transition: all 0.2s ease !important;
            display: inline-flex;
            align-items: center;
        }

        a.text-muted:hover {
            color: #60a5fa !important;
            transform: translateX(-4px);
        }

        /* Corrección para selects deshabilitados de Bootstrap */
        .form-select-premium:disabled {
            background-color: rgba(15, 17, 26, 0.4) !important;
            opacity: 0.5;
            color: rgba(255, 255, 255, 0.3) !important;
        }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0 split-container">
            
            <!-- Branding Side (Visible solo en desktop) -->
            <div class="col-lg-5 branding-side d-none d-lg-flex">
                <div class="mb-5">
                    <span class="fs-3 fw-extrabold text-white d-flex align-items-center">
                        <i class="bi bi-ticket-perforated-fill theme-text-primary me-3 fs-1"></i> HelpDesk
                    </span>
                </div>
                <div class="my-auto">
                    <h1 class="display-4 fw-extrabold text-white mb-4" style="line-height: 1.15;">
                        Registra tu acceso a la <br><span class="theme-text-primary">Mesa de Control</span>
                    </h1>
                    <p class="text-muted fs-5 mb-5" style="max-width: 480px;">
                        Solicita tu cuenta empresarial. Tu solicitud será evaluada por el equipo de Gestión para autorizar tu acceso al sistema de incidencias.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-shield-check theme-text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Seguridad Garantizada</h6>
                                <small class="text-muted">Aprobación manual obligatoria para cada cuenta.</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-diagram-3 theme-text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Organización Dinámica</h6>
                                <small class="text-muted">Soporte multi-institucional integrado en cascada.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-auto">
                    <small class="text-muted">&copy; 2026 HelpDesk Global Inc. Todos los derechos reservados.</small>
                </div>
            </div>

            <!-- Form Side -->
            <div class="col-lg-7 form-side">
                <div class="form-card">
                    
                    <div class="text-center mb-4">
                        <h3 class="fw-extrabold text-white mb-1"><i class="bi bi-person-plus-fill theme-text-primary me-2"></i> Crear Cuenta</h3>
                        <p class="small text-muted">Completa tu información personal y laboral para registrarte.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-4 p-3 mb-4">
                            <ul class="mb-0 small fw-bold">
                                @foreach ($errors->all() as $error)
                                    <li><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- SECCIÓN 1: DATOS PERSONALES -->
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge-fill me-2"></i> Datos Personales</h6>
                        <div class="row g-3 mb-4">
                            <!-- Cedula -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Cédula de Identidad</label>
                                <input type="text" name="cedula" class="form-control form-control-premium" required value="{{ old('cedula') }}">
                            </div>
                            <!-- Telefono -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Número de Teléfono</label>
                                <input type="text" name="telefono" class="form-control form-control-premium" required value="{{ old('telefono') }}">
                            </div>
 
                            <!-- Nombres -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Primer Nombre</label>
                                <input type="text" name="nombre" class="form-control form-control-premium" required value="{{ old('nombre') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Segundo Nombre (Opcional)</label>
                                <input type="text" name="segundo_nombre" class="form-control form-control-premium" value="{{ old('segundo_nombre') }}">
                            </div>
 
                            <!-- Apellidos -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Primer Apellido</label>
                                <input type="text" name="apellido" class="form-control form-control-premium" required value="{{ old('apellido') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Segundo Apellido (Opcional)</label>
                                <input type="text" name="segundo_apellido" class="form-control form-control-premium" value="{{ old('segundo_apellido') }}">
                            </div>
                        </div>

                        <!-- SECCIÓN 2: UBICACIÓN INSTITUCIONAL -->
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building-fill me-2"></i> Ubicación Institucional</h6>
                        
                        <div class="row g-3 mb-4" id="seccion-cascada">
                            @if($nivelesActivos->count() > 0)
                                <!-- Primer Nivel (Siempre visible) -->
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">1. {{ $nivelesActivos[0]->nombre }}</label>
                                    <select id="select-nivel-1" class="form-select form-select-premium select-cascada" data-index="1" required>
                                        <option value="">Seleccione un(a) {{ $nivelesActivos[0]->nombre }}...</option>
                                        @foreach($unidadesNivel1 as $unidad)
                                            <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Niveles Secundarios (Generados estáticamente, ocultos inicialmente) -->
                                @foreach($nivelesActivos as $index => $nivel)
                                    @if($index > 0)
                                        <div class="col-12 wrapper-cascada" id="wrapper-nivel-{{ $index + 1 }}" style="display: none;">
                                            <label class="form-label text-muted small fw-semibold">{{ $index + 1 }}. {{ $nivel->nombre }}</label>
                                            <select id="select-nivel-{{ $index + 1 }}" class="form-select form-select-premium select-cascada" data-index="{{ $index + 1 }}">
                                                <option value="">Seleccione un(a) {{ $nivel->nombre }}...</option>
                                            </select>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="col-12 text-center py-3 border border-dashed rounded-4 text-warning bg-warning bg-opacity-10">
                                    <i class="bi bi-exclamation-triangle fs-3 d-block mb-1"></i>
                                    <small>El sistema aún no tiene configuraciones organizativas creadas. Por favor contacte al soporte técnico.</small>
                                </div>
                            @endif

                            <!-- Campo Oculto para enviar el ID de la unidad seleccionada en el formulario -->
                            <input type="hidden" name="unidad_id" id="input_unidad_id" required>
                        </div>

                        <!-- SECCIÓN 3: CREDENCIALES -->
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-key-fill me-2"></i> Credenciales de Acceso</h6>
                        <div class="row g-3 mb-4">
                            <!-- Correo -->
                            <div class="col-12">
                                <label class="form-label text-muted small fw-semibold">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control form-control-premium" required value="{{ old('email') }}">
                            </div>
 
                            <!-- Contraseña -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Contraseña</label>
                                <input type="password" name="password" class="form-control form-control-premium" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-premium" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a class="text-muted small text-decoration-none fw-medium" href="{{ route('login') }}">
                                <i class="bi bi-arrow-left me-1"></i> ¿Ya tienes una cuenta? Iniciar Sesión
                            </a>
                            <button type="submit" class="btn btn-premium px-4">
                                Enviar Solicitud <i class="bi bi-send ms-2"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX Cascading Dropdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectCascadas = document.querySelectorAll('.select-cascada');
            const hiddenUnidadInput = document.getElementById('input_unidad_id');
            const totalNiveles = {{ $nivelesActivos->count() }};

            selectCascadas.forEach(select => {
                select.addEventListener('change', function () {
                    const currentIndex = parseInt(this.getAttribute('data-index'));
                    const selectedValue = this.value;

                    // 1. Limpiar e inicializar todos los selects hijos más profundos en la jerarquía
                    for (let i = currentIndex + 1; i <= totalNiveles; i++) {
                        const childSelect = document.getElementById(`select-nivel-${i}`);
                        const childWrapper = document.getElementById(`wrapper-nivel-${i}`);
                        if (childSelect) {
                            childSelect.innerHTML = `<option value="">Seleccione una opción...</option>`;
                            childSelect.required = false; // Desactivar temporalmente
                            if (childWrapper) {
                                childWrapper.style.display = 'none'; // Esconderlos
                            }
                        }
                    }

                    // 2. Si se vació la opción, el ID a enviar pasa a ser el del padre (si existe) o vacío
                    if (!selectedValue) {
                        if (currentIndex > 1) {
                            const parentSelect = document.getElementById(`select-nivel-${currentIndex - 1}`);
                            hiddenUnidadInput.value = parentSelect.value;
                        } else {
                            hiddenUnidadInput.value = '';
                        }
                        return;
                    }

                    // 3. Asignar el valor actual como el seleccionado del organigrama
                    hiddenUnidadInput.value = selectedValue;

                    // 4. Si hay un nivel siguiente configurado, traer sus hijos vía AJAX
                    const nextIndex = currentIndex + 1;
                    const nextSelect = document.getElementById(`select-nivel-${nextIndex}`);
                    const nextWrapper = document.getElementById(`wrapper-nivel-${nextIndex}`);

                    if (nextSelect) {
                        fetch(`/unidades-hijas/${selectedValue}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    // Rellenar las opciones del hijo
                                    let options = `<option value="">Seleccione una opción...</option>`;
                                    data.forEach(item => {
                                        options += `<option value="${item.id}">${item.nombre}</option>`;
                                    });
                                    nextSelect.innerHTML = options;
                                    
                                    // Hacer obligatorio el siguiente campo y mostrarlo
                                    nextSelect.required = true;
                                    nextWrapper.style.display = 'block';
                                } else {
                                    // Si no tiene hijos en el organigrama, este es el nodo final
                                    hiddenUnidadInput.value = selectedValue;
                                }
                            })
                            .catch(error => {
                                console.error('Error al cargar la cascada del organigrama:', error);
                            });
                    }
                });
            });
        });
    </script>
</body>
</html>
