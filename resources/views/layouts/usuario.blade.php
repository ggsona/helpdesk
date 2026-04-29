<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soporte GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { 
            --bg-main: #f0f2f5; 
            --card-bg: #ffffff;
            --text-main: #1a1c1e;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --input-bg: #ffffff;
        }

        [data-bs-theme="dark"] { 
            --bg-main: #0b0c0d; 
            --card-bg: #111214;
            --text-main: #e9ecef;
            --text-muted: #a0a5aa;
            --border-color: #2d2f31;
            --input-bg: #1a1c1e;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main) !important; 
            color: var(--text-main);
            transition: all 0.3s ease; 
        }

        /* Card Premium mejorada */
        .card-premium {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 16px;
            color: var(--text-main) !important;
            transition: all 0.3s ease;
        }

        .hover-shadow:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        }

        /* Texto adaptable */
        .theme-text { color: var(--text-main) !important; }
        .theme-muted { color: var(--text-muted) !important; }

        /* Inputs Premium que no se quedan blancos */
        .form-control-premium {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .form-control-premium:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
        }

        /* Contenedor de subida de archivos */
        .dupload-container {
            background-color: var(--bg-main) !important;
            border: 2px dashed var(--border-color) !important;
        }

        /* Navbar adaptable */
        .navbar { 
            background-color: var(--card-bg) !important; 
            border-bottom: 1px solid var(--border-color) !important; 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('usuario.home') }}">
                <i class="bi bi-headset me-2"></i>GDC <span class="text-body-secondary">Soporte</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navUsuario">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navUsuario">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.home') ? 'active text-primary' : '' }}" 
                        href="{{ route('usuario.home') }}">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.tickets.create') ? 'active text-primary' : '' }}" 
                        href="{{ route('usuario.tickets.create') }}">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Ticket
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.tickets.index') ? 'active text-primary' : '' }}" 
                        href="{{ route('usuario.tickets.index') }}">
                            <i class="bi bi-ticket-perforated me-1"></i> Mis Casos
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-body p-0" onclick="toggleTheme()">
                        <i class="bi bi-circle-half"></i>
                    </button>

                    <div class="dropdown">
                        <button class="btn border-0 d-flex align-items-center p-0" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 35px; height: 35px;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                            <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Salir</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
        }
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>