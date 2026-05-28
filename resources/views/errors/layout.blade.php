<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error del sistema')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", savedTheme);
    </script>
    <style>
        :root {
            --err-bg: #f4f7fa;
            --err-card: #ffffff;
            --err-text: #212529;
            --err-muted: #6c757d;
            --err-border: #e9ecef;
        }
        [data-bs-theme="dark"] {
            --err-bg: #0b0c0d;
            --err-card: #17191b;
            --err-text: #f8f9fa;
            --err-muted: #adb5bd;
            --err-border: #2d2f31;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--err-bg);
            color: var(--err-text);
            font-family: "Inter", system-ui, -apple-system, sans-serif;
        }
        .error-card {
            width: min(92vw, 560px);
            background: var(--err-card);
            border: 1px solid var(--err-border);
            border-radius: 18px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.08);
        }
        .error-icon {
            font-size: 3.2rem;
            margin-bottom: 0.75rem;
            color: var(--accent, #0d6efd);
        }
        .error-code {
            font-size: 4.8rem;
            line-height: 1;
            font-weight: 800;
            color: var(--accent, #0d6efd);
            margin-bottom: 0.65rem;
        }
        .error-message {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .error-description {
            color: var(--err-muted);
            margin-bottom: 1.35rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="bi @yield('icon', 'bi-exclamation-circle')"></i></div>
        <div class="error-code">@yield('code', 'Error')</div>
        <div class="error-message">@yield('message', 'Ha ocurrido un error')</div>
        <p class="error-description">@yield('description', 'Intenta nuevamente en unos minutos.')</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            @yield('actions')
            <a href="{{ url('/') }}" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>
        @hasSection('extra')
            <div class="mt-3 text-start small text-muted">
                @yield('extra')
            </div>
        @endif
    </div>
</body>
</html>
