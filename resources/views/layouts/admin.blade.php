<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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
            font-family: 'Inter', sans-serif; 
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
        }

        .nav-link { 
            color: var(--bs-body-color) !important; 
            font-size: 0.88rem; 
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 2px 10px;
        }

        .nav-link:hover { background: var(--bs-secondary-bg); }
        .nav-link.active { background: #0d6efd !important; color: #fff !important; }

        /* Contenedor de Contenido */
        #content { flex-grow: 1; padding: 2.5rem; }

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

        /* Footer de Usuario fijo abajo */
        .user-footer {
            margin-top: auto; /* Empuja hacia abajo */
            padding: 1.2rem;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
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
    </style>
</head>
<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div class="p-4 mb-2">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h5>
            </div>

            <div class="flex-grow-1">
                <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Panel Operativo</small>
                
                <ul class="nav nav-pills flex-column">
                    {{-- DASHBOARD --}}
                    <li>
                        @role('admin')
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        @endrole
                        @role('gestor')
                            <a href="{{ route('gestor.dashboard') }}" class="nav-link {{ request()->routeIs('gestor.dashboard') ? 'active' : '' }}">
                        @endrole
                        @role('tecnico')
                            <a href="{{ route('tecnico.dashboard') }}" class="nav-link {{ request()->routeIs('tecnico.dashboard') ? 'active' : '' }}">
                        @endrole
                            <i class="bi bi-grid-1x2-fill me-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- GESTIÓN DE TICKETS --}}
                    <li>
                        @hasanyrole('admin|gestor')
                            <a href="{{ route('gestor.tickets.index') }}" class="nav-link {{ request()->routeIs('gestor.tickets.*') ? 'active' : '' }}">
                                <i class="bi bi-ticket-detailed-fill me-3"></i>Gestión de Casos
                            </a>
                        @else
                            <a href="#" class="nav-link">
                                <i class="bi bi-ticket-perforated me-3"></i>Casos Asignados
                            </a>
                        @endhasanyrole
                    </li>

                    {{-- MÓDULOS COMPARTIDOS (ADMIN Y GESTOR) --}}
                    @hasanyrole('admin|gestor')
                        <hr class="mx-3 my-2 opacity-25 text-muted">
                        <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Configuración y Activos</small>

                        <li><a href="#" class="nav-link"><i class="bi bi-people-fill me-3"></i>Usuarios</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-building-fill me-3"></i>Oficinas</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-tags-fill me-3"></i>Categorías</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-pc-display me-3"></i>Asignación de Equipos</a></li>
                        
                        <li><a href="#" class="nav-link"><i class="bi bi-bar-chart-line-fill me-3"></i>Rendimiento Técnico</a></li>
                    @endhasanyrole
                </ul>
            </div>

            <div class="user-footer">
                <div class="dropend">
                    <button class="btn border-0 d-flex align-items-center w-100 p-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ms-3 text-start">
                            <p class="mb-0 fw-bold small text-truncate" style="max-width: 120px;">{{ Auth::user()->name }}</p>
                            <small class="text-muted" style="font-size: 0.7rem;">
                                @if(Auth::user()->hasRole('admin')) Administrador (Sistemas)
                                @elseif(Auth::user()->hasRole('gestor')) Gestor de Soporte
                                @else Técnico
                                @endif
                            </small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 mb-2">
                        <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        <li><button class="dropdown-item py-2" onclick="toggleTheme()"><i class="bi bi-moon-stars me-2"></i> Cambiar Tema</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
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
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
        }
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</body>
</html>