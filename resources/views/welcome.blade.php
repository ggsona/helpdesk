<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk - Soporte Técnico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            height: 100vh;
        }
        .hero-section { height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-welcome { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            background: #ffffff;
        }
        .btn-primary { 
            background-color: #0d6efd; 
            border: none; 
            padding: 12px 35px; 
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
        .text-gradient {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>

    <div class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5 text-center">
                    <div class="card card-welcome p-5">
                        <div class="mb-4">
                            <div class="bg-primary bg-opacity-10 d-inline-block p-4 rounded-circle">
                                <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h1 class="fw-bold text-dark mb-2">Helpdesk <span class="text-gradient">GDC</span></h1>
                        <p class="text-muted mb-5">Sistema centralizado para la gestión de incidencias y soporte técnico.</p>
                        
                        <div class="d-grid gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">
                                        <i class="bi bi-house-door me-2"></i> Entrar al Panel
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg shadow">
                                        Iniciar Sesión
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn btn-outline-dark btn-lg">
                                            Crear Cuenta
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>