<x-cliente-layout>
    <div class="row mb-5">
        <div class="col-md-8">
            <h2 class="fw-bold">Hola, {{ Auth::user()->name }} 👋</h2>
            <p class="text-muted fs-5">¿Cómo podemos ayudarte con tu equipo hoy?</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card-premium p-4 h-100 text-center shadow-sm">
                <div class="bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-chat-left-dots fs-2"></i>
                </div>
                <h5 class="fw-bold">Abrir Ticket</h5>
                <p class="text-muted small">Reporta fallas técnicas o solicita nuevos insumos.</p>
                <a href="{{ route('cliente.tickets.create') }}" class="btn btn-primary w-100 rounded-pill fw-bold">
                    Comenzar
                </a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card-premium p-4 h-100 text-center shadow-sm">
                <div class="bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-list-check fs-2"></i>
                </div>
                <h5 class="fw-bold">Mis Solicitudes</h5>
                <p class="text-muted small">Revisa el estado de tus reportes anteriores.</p>
                <a href="{{ route('cliente.tickets.index') }}" class="btn btn-success w-100 rounded-pill fw-bold text-white">
                    Ver Historial
                </a>
            </div>
        </div>
    </div>
</x-cliente-layout>