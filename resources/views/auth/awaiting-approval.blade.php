<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Acceso Pendiente | {{ config('app.name', 'HelpDesk') }}</title>

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
            overflow-y: auto; /* Permitir scroll si el alto de pantalla es corto */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem; /* Espaciado de seguridad */
        }

        /* Fondo dinámico y brillos */
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            top: 10%;
            left: 10%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            bottom: 10%;
            right: 10%;
            pointer-events: none;
        }

        .waiting-card {
            background-color: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem 2rem; /* Más compacto para optimizar altura */
            width: 100%;
            max-width: 500px; /* Reducción de ancho para mayor simetría */
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 10;
        }

        .glowing-icon-container {
            width: 90px;
            height: 90px;
            background: radial-gradient(circle, rgba(234, 179, 8, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse-glow 2s infinite ease-in-out;
        }

        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
                opacity: 0.9;
            }
            50% {
                transform: scale(1.08);
                opacity: 1;
            }
        }

        .btn-outline-premium {
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-premium:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Corrección de color de texto para modo oscuro corporativo */
        .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .text-secondary {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        .small.text-muted {
            color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>
</head>
<body>

    <div class="waiting-card">
        
        <!-- Icono Reluciente de Reloj de Arena -->
        <div class="glowing-icon-container">
            <i class="bi bi-hourglass-split text-warning fs-1"></i>
        </div>

        <!-- Mensaje de Estado -->
        <h3 class="fw-extrabold text-white mb-2">Solicitud en Revisión</h3>
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2 mb-4 fw-semibold" style="font-size: 0.8rem;">
            <i class="bi bi-shield-fill-exclamation me-1"></i> Pendiente de Aprobación
        </span>

        <p class="text-muted mb-4 fs-6" style="line-height: 1.6;">
            ¡Hola, <strong class="text-white">{{ auth()->user()->name }}</strong>! Tu registro se ha completado exitosamente y ha sido enviado a la mesa de control de incidentes.
        </p>

        <!-- Cuadro Informativo de Ubicación -->
        <div class="border border-secondary border-opacity-10 rounded-4 p-3 bg-dark bg-opacity-20 mb-4 text-start">
            <h6 class="text-primary fw-bold small mb-2"><i class="bi bi-building-fill me-2"></i> Datos Registrados:</h6>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted small">Cédula:</span>
                <span class="small fw-semibold">{{ auth()->user()->persona->cedula ?? 'No asignada' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted small">Teléfono:</span>
                <span class="small fw-semibold">{{ auth()->user()->persona->telefono ?? 'No asignado' }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted small">Departamento:</span>
                <span class="small fw-semibold text-truncate" style="max-width: 250px;">
                    {{ auth()->user()->persona->unidadAdministrativa->nombre ?? 'Sin especificar' }}
                </span>
            </div>
        </div>

        <p class="small text-muted mb-4">
            Por favor, ponte en contacto con tu supervisor o administrador de sistemas para acelerar la activación de tu cuenta.
        </p>

        <!-- Botón de Salida Segura (LogOut) -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-premium w-100 fw-bold">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </button>
        </form>

    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
