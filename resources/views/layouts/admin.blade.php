<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", savedTheme);
    </script>
    
    @stack("styles") {{-- Para CSS de vistas específicas --}}

    <style>
        :root { 
            --sb-width: 270px;
            --bg-main: #f4f7fa; /* Gris claro que no cansa la vista */
            --sb-bg: #ffffff;
        }

        [data-bs-theme="dark"] {
            --bg-main: #0b0c0d;
            --sb-bg: #111214;
        }
        body {
            font-family: "Inter", sans-serif; 
            background-color: var(--bg-main) !important; 
            transition: all 0.3s ease;
        }
        
        /* Sidebar Fijo y Estructurado */
        #sidebar {
            width: var(--sb-width); 
            min-width: var(--sb-width); 
            height: 100vh;
            background: var(--sb-bg);
            border-right: 1px solid var(--bs-border-color);
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column; /* Permite empujar el footer al fondo */
            z-index: 1000;
            overflow: hidden; /* Evitar que el sidebar completo se desborde */
        }

        /* Scrollbar premium y minimalista para el menú interno */
        .sidebar-scrollable {
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .sidebar-scrollable::-webkit-scrollbar{ 
            width: 4px;
        }
        .sidebar-scrollable::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scrollable::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        [data-bs-theme="dark"] .sidebar-scrollable::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Estilos del menú desplegable del pie de página (Dropup) */
        .user-footer .dropdown-menu {
            position: absolute !important;
            bottom: 100% !important;
            left: 0 !important;
            width: 100% !important;
            margin-bottom: 8px !important;
            border: 1px solid var(--bs-border-color) !important;
            background-color: var(--sb-bg) !important;
            z-index: 1100;
        }
        .user-footer .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        [data-bs-theme="dark"] .user-footer .dropdown-menu {
            background-color: #1e293b !important;
        }

        .nav-link {
            color: var(--bs-body-color) !important; 
            font-size: 0.88rem; 
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 2px 10px;
        }

        .nav-link:hover {
            background: var(--bs-secondary-bg);
        }
        .nav-link.active {
            background: #0d6efd !important; color: #fff !important;
        }

        /* Contenedor de Contenido */
        #content {
            flex-grow: 1; padding: 2.5rem 2.5rem 2.5rem 1.5rem;
        }

        /* Tarjeta Premium que reacciona al tema */
        .card-premium {
            background: var(--bs-custom-card-bg, #fff);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .card-premium {
            background: #1a1c1e;
        }
        
        /* Anular bordes de focus nativos del navegador */
        .card-premium:focus, .card-premium:active, .card-premium:focus-visible {
            outline: none !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        /* Prevenir que el contenido desborde las tarjetas en cualquier pantalla */
        .card-premium {
            overflow: hidden;
            min-width: 0;
            word-break: break-word;
        }

        /* Asegurar que las columnas del grid respeten sus límites */
        .row > [class*="col-"] {
            min-width: 0;
        }

        /* Truncar texto de una línea sin romper el layout */
        .text-truncate-safe {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            display: block;
        }

        /* Fechas y textos sin espacios: romper con overflow-wrap */
        .text-overflow-wrap {
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }

        /* En pantallas menores a 768px, las columnas siempre ocupan el ancho completo */
        @media (max-width: 767.98px) {
            .col-md-4, .col-md-8 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        /* Responsive Sidebar y Mobile Header */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed !important;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1045 !important;
                box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            }
            #sidebar.show {
                transform: translateX(0);
            }
            #content {
                padding: 1.5rem !important;
                width: 100%;
            }
            /* Backdrop al abrir el sidebar */
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(4px);
                z-index: 1040;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            .sidebar-backdrop.show 
            {
                opacity: 1;
                visibility: visible;
            }
            /* Header móvil visible únicamente en pantallas pequeñas */
            .mobile-header {
                display: flex !important;
                align-items: center;
                background: var(--sb-bg);
                border-bottom: 1px solid var(--bs-border-color);
                padding: 0.8rem 1.5rem;
                position: sticky;
                top: 0;
                z-index: 1030;
                width: 100%;
                transition: background-color 0.3s ease;
            }
        }
        @media (min-width: 992px) {
            .mobile-header {
                display: none !important;
            }
        }

        /* Footer de Usuario fijo abajo */
        .user-footer {
            margin-top: auto; /* Empuja hacia abajo */
            padding: 1.2rem;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
            position: relative;
            z-index: 1050; /* Garantiza que se dibuje por encima de la lista de scroll */
        }

        /* Estilos para formularios Premium */
        .form-control-premium {
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }
        .form-control-premium:focus {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .section-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Tarjetas de sección */
        .profile-card {
            background-color: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Estilo para los Labels (Nombres de campos) */
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            margin-bottom: 0.5rem;
        }

        /* Inputs Premium */
        .form-control-premium {
            background-color: var(--bs-body-bg) !important;
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color) !important;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .form-control-premium:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        /* Estilos premium para la paginación (Soporta Dark y Light Mode) */
        .pagination .page-link {
            background-color: var(--bs-custom-card-bg, #fff) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
            border-radius: 8px !important;
            margin: 0 3px !important;
            padding: 8px 16px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        [data-bs-theme="dark"] .pagination .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .pagination .page-link:hover {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: #0d6efd !important;
        }

        /* Estandar premium para listados de Admin/Soporte */
        .card-premium .table {
            margin-bottom: 0;
        }
        .card-premium .table thead th {
            border-bottom: 1px solid color-mix(in srgb, var(--bs-border-color) 85%, transparent);
            color: var(--bs-secondary-color);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }
        .card-premium .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .card-premium .table tbody tr:hover {
            background-color: color-mix(in srgb, var(--bs-primary-bg-subtle) 30%, transparent);
        }
        .card-premium .table td {
            vertical-align: middle;
        }
        .card-premium .table .btn {
            white-space: nowrap;
        }
        .btn-action-premium {
            border-radius: 10px !important;
            font-weight: 600;
            min-height: 34px;
            white-space: nowrap;
        }
        .btn-action-icon {
            min-width: 34px;
            min-height: 34px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .table .d-flex.gap-2 {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* Estándar de columnas de tabla heredado de SIGEINV */
        .th-id {
            width: 80px;
            min-width: 80px;
        }
        .th-estado {
            width: 120px;
            min-width: 120px;
        }
        .th-acciones {
            width: 140px;
            min-width: 140px;
            text-align: right;
        }

    </style>
</head>
<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div class="p-4 mb-2">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h5>
            </div>

            <div class="sidebar-scrollable">
                <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Panel Operativo</small>
                
                <ul class="nav nav-pills flex-column">
                    {{-- DASHBOARD COMPARTIDO --}}
                    <li>
                        <a href="{{ route("soporte.dashboard") }}" class="nav-link {{ request()->routeIs("soporte.dashboard") ? "active" : "" }}">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- GESTIÓN DE TICKETS SEGÚN ROL --}}
                    @can("asignar-tickets")
                        <li>
                            <a href="{{ route("soporte.tickets.index") }}" class="nav-link {{ request()->routeIs("soporte.tickets.*") && !request()->routeIs("soporte.tickets.tecnico.*") ? "active" : "" }}">
                                <i class="bi bi-ticket-detailed-fill me-3"></i>Mesa de Despacho
                            </a>
                        </li>
                    @endcan
                    
                    @can("resolver-tickets")
                        <li>
                            <a href="{{ route("soporte.tickets.tecnico.index") }}" class="nav-link {{ request()->routeIs("soporte.tickets.tecnico.*") ? "active" : "" }}">
                                <i class="bi bi-ticket-perforated me-3"></i>Mis Tareas Activas
                            </a>
                        </li>
                    @endcan

                    {{-- CONTROL DE ACCESOS (SÓLO ADMINISTRADORES) --}}
                    @if(auth()->user()->can("gestionar-roles") || auth()->user()->can("gestionar-usuarios"))
                        <hr class="mx-3 my-2 opacity-25 text-muted">
                        <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Control de Accesos</small>
                        
                        @can("gestionar-roles")
                        <li>
                            <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->routeIs("admin.roles.*") ? "active" : "" }}">
                                <i class="bi bi-shield-lock-fill me-3"></i>Roles y Permisos
                            </a>
                        </li>
                        @endcan
                        
                        @can("gestionar-usuarios")
                        <li>
                            @php
                                $pendientesCount = \App\Models\User::where("is_approved", false)->count();
                            @endphp
                            <a href="{{ route("admin.usuarios.pendientes") }}" class="nav-link {{ request()->routeIs("admin.usuarios.pendientes") ? "active" : "" }} d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-person-fill-gear me-3"></i>Aprobación de Usuarios
                                </span>
                                @if($pendientesCount > 0)
                                    <span class="badge bg-warning text-dark rounded-pill fw-bold" style="font-size: 0.7rem; padding: 0.25em 0.6em;">
                                        {{ $pendientesCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        @endcan
                    @endif

                    {{-- CONFIGURACIÓN Y ACTIVOS --}}
                    @if(auth()->user()->can("gestionar-usuarios") || auth()->user()->can("ver-configuraciones") || auth()->user()->can("asignar-tickets") || auth()->user()->can("gestionar-categorias") || auth()->user()->can("ver-rendimiento-tecnico") || auth()->user()->can("ver-auditorias"))
                        <hr class="mx-3 my-2 opacity-25 text-muted">
                        <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Configuración y Activos</small>

                        @can("gestionar-usuarios")
                        <li><a href="{{ route("admin.usuarios.index") }}" class="nav-link {{ request()->routeIs("admin.usuarios.index") ? "active" : "" }}"><i class="bi bi-people-fill me-3"></i>Usuarios</a></li>
                        @endcan

                        @can("ver-configuraciones")
                        <li><a href="{{ route("admin.estructura.index") }}" class="nav-link {{ request()->routeIs("admin.estructura.*") ? "active" : "" }}"><i class="bi bi-diagram-3-fill me-3"></i>Organigrama</a></li>
                        @endcan
                        
                        @can("gestionar-categorias")
                         <li>
                            <a href="{{ route("admin.categorias.index") }}" class="nav-link {{ request()->routeIs("admin.categorias.*") ? "active" : "" }}">
                                <i class="bi bi-tags-fill me-3"></i>Categorías de Tickets
                            </a>
                        </li>
                        @endcan
                        @can("gestionar-equipos")
                        <li>
                            <a href="{{ route("admin.equipos.index") }}" class="nav-link {{ request()->routeIs("admin.equipos.*") && !request()->routeIs("admin.equipos.catalogos.*") ? "active" : "" }}">
                                <i class="bi bi-pc-display me-3"></i>Asignación de Equipos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route("admin.equipos.catalogos.index") }}" class="nav-link {{ request()->routeIs("admin.equipos.catalogos.*") ? "active" : "" }}">
                                <i class="bi bi-list-stars me-3"></i>Catálogo de Activos
                            </a>
                        </li>
                        @endcan
                        @can('ver-rendimiento-tecnico')
                            <li>
                                <a href="{{ route('admin.rendimiento.index') }}" class="nav-link {{ request()->routeIs('admin.rendimiento.*') ? 'active' : '' }}">
                                    <i class="bi bi-bar-chart-line-fill me-3"></i>Rendimiento Técnico
                                </a>
                            </li>
                        @endcan
                        @can('ver-auditorias')
                            <li>
                                <a href="{{ route('admin.auditorias.index') }}" class="nav-link {{ request()->routeIs('admin.auditorias.*') ? 'active' : '' }}">
                                    <i class="bi bi-journal-text me-3"></i>Bitácora de Auditorías
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>
            </div>

            <div class="user-footer">
                <div class="dropup" style="position: relative; z-index: 1050;">
                    <button class="btn border-0 d-flex align-items-center w-100 p-0" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ms-3 text-start">
                            <p class="mb-0 fw-bold small text-truncate" style="max-width: 120px;">{{ Auth::user()->name }}</p>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.6rem; letter-spacing: 0.3px;">
                                {{ Auth::user()->roles->pluck("name")->implode(", ") ?: "Soporte" }}
                            </small>
                        </div>
                        <i class="bi bi-chevron-up ms-auto text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 mb-2">
                        <li><a class="dropdown-item py-2" href="{{ route("profile.edit") }}"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        
                        @can("ver-configuraciones")
                        <li><a class="dropdown-item py-2" href="{{ route("admin.configuraciones.index") }}"><i class="bi bi-gear-fill me-2 text-secondary"></i> Ajustes del Sistema</a></li>
                        @endcan
                        
                        <li><button class="dropdown-item py-2" onclick="toggleTheme()"><i class="bi bi-moon-stars me-2"></i> Cambiar Tema</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route("logout") }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main id="content">
            <!-- Header Móvil para pantallas táctiles/pequeñas -->
            <div class="mobile-header d-none justify-content-between align-items-center mb-3 rounded-3 shadow-sm border border-secondary border-opacity-10">
                <button class="btn btn-outline-secondary border-0 p-1 shadow-none" id="btn-toggle-sidebar">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h6>
                <div style="width: 32px;"></div> <!-- Nivelador óptico -->
            </div>

            <div class="container-fluid">
                @yield("content")
                @if(isset($slot))
                    {{ $slot }} {{-- Para la vista de perfil que usa x-dynamic-component --}}
                @endif
            </div>
        </main>
    </div>

    <!-- Backdrop para cerrar menú táctil en móviles al pulsar fuera -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> {{-- Añadir jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
            html.setAttribute("data-bs-theme", target);
            localStorage.setItem("theme", target);
        }

        // Control del sidebar responsivo
        document.addEventListener("DOMContentLoaded", function() {
            const btnToggle = document.getElementById("btn-toggle-sidebar");
            const sidebar = document.getElementById("sidebar");
            const backdrop = document.getElementById("sidebar-backdrop");
            
            if (btnToggle && sidebar && backdrop) {
                btnToggle.addEventListener("click", function() {
                    sidebar.classList.add("show");
                    backdrop.classList.add("show");
                });
                
                backdrop.addEventListener("click", function() {
                    sidebar.classList.remove("show");
                    backdrop.classList.remove("show");
                });
            }
        });
    </script>
    @stack("scripts") {{-- Para JS de vistas específicas --}}
</body>
</html>
