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
        :root { --bg-main: #f4f7fa; }
        [data-bs-theme="dark"] { --bg-main: #0b0c0d; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main) !important; 
            transition: 0.3s; 
        }

        .navbar { 
            background-color: var(--bs-tertiary-bg); 
            border-bottom: 1px solid var(--bs-border-color); 
        }

        /* Cards Premium y Efectos Hover */
        .card-premium, .hover-shadow {
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-premium:hover, .hover-shadow:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        /* Personalización del Scrollbar para el Chat */
        .chat-container::-webkit-scrollbar {
            width: 6px;
        }
        .chat-container::-webkit-scrollbar-thumb {
            background-color: rgba(128, 128, 128, 0.2);
            border-radius: 10px;
        }
        .chat-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .card-premium, 
        .card-body, 
        .text-muted-custom, /* Si usas una clase personalizada para descripciones */
        h1, h2, h3, h4, h5, h6, label, p {
            /* Si el contenedor es oscuro como en tu imagen, el texto debe ser blanco */
            color: #ffffff !important; 
        }

        /* Si quieres ser más específico solo para la zona de contenido del ticket */
        .show-ticket-content {
            color: #ffffff !important;
        }

        /* Ajuste para inputs o áreas de texto deshabilitadas que se ven grises */
        .form-control:disabled, .form-control[readonly] {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff !important;
            border-color: var(--bs-border-color);
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