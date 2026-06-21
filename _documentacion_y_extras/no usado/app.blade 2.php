<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - Helpdesk GDC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root { 
            --sidebar-bg: #ffffff; 
            --active-bg: #f0f4ff; 
            --active-color: #0d6efd; 
        }

        body { 
            background-color: #fcfcfd; 
            font-family: 'Inter', sans-serif; 
            color: #1a1d23;
        }

        #sidebar { 
            min-width: 260px; 
            max-width: 260px; 
            height: 100vh; 
            background: var(--sidebar-bg); 
            position: sticky;
            top: 0;
        }

        /* Reducción de tamaño de textos en el menú */
        #sidebar .nav-link { 
            font-size: 0.85rem; 
            font-weight: 500;
            transition: all 0.2s ease;
        }

        #sidebar .nav-link:hover { 
            background-color: #f8f9fa;
            color: #000 !important;
        }

        #sidebar .nav-link.active { 
            background-color: var(--active-bg); 
            color: var(--active-color) !important; 
            font-weight: 600; 
        }

        /* Dropdown Premium Style */
        .dropdown-item { font-size: 0.85rem; font-weight: 500; }
        .dropdown-item:active { background-color: var(--active-color); }
        
        #content { width: 100%; padding: 2rem; }
        
        /* Efecto suave para el Dropend */
        .dropend .dropdown-toggle::after { display: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar" class="d-flex flex-column flex-shrink-0 border-end shadow-sm">
            <div class="sidebar-header py-4 px-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-headset text-primary me-2"></i>Helpdesk <span class="text-primary text-opacity-75">GDC</span></h5>
            </div>

            <div class="px-2 flex-grow-1 overflow-auto">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item mb-1">
                        <small class="text-uppercase text-muted fw-semibold ps-3 mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">General</small>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-dark' }} rounded-3 py-2">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Dashboard
                        </a>
                    </li>

                    <li class="nav-item mt-4">
                        <small class="text-uppercase text-muted fw-semibold ps-3 mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Administración</small>
                        <a href="#" class="nav-link text-dark rounded-3 py-2 mb-1">
                            <i class="bi bi-ticket-detailed-fill me-3"></i>Gestión de Tickets
                        </a>
                        <a href="#" class="nav-link text-dark rounded-3 py-2 mb-1">
                            <i class="bi bi-people-fill me-3"></i>Usuarios
                        </a>
                        <a href="#" class="nav-link text-dark rounded-3 py-2 mb-1">
                            <i class="bi bi-building-fill me-3"></i>Oficinas
                        </a>
                        <a href="#" class="nav-link text-dark rounded-3 py-2 mb-1">
                            <i class="bi bi-pc-display me-3"></i>Asignación Dispositivos
                        </a>
                    </li>
                </ul>
            </div>

            <div class="border-top p-3 bg-light bg-opacity-50">
                <div class="dropend">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ms-3 overflow-hidden">
                            <p class="mb-0 fw-bold text-truncate" style="font-size: 0.85rem;">{{ Auth::user()->name }}</p>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Administrador</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu shadow-lg border-0 ms-2 py-2" style="min-width: 200px; border-radius: 12px;">
                        <li><h6 class="dropdown-header text-muted">Ajustes de Cuenta</h6></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Configuración</a></li>
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

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-custom mb-4 rounded">
                <div class="container-fluid">
                    <span class="navbar-text fw-bold">
                        Bienvenido, {{ Auth::user()->name }}
                    </span>
                </div>
            </nav>

            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>