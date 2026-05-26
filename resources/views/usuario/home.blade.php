<x-usuario-layout>
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
                <a href="{{ route('usuario.tickets.create') }}" class="btn btn-primary w-100 rounded-pill fw-bold">
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
                <a href="{{ route('usuario.tickets.index') }}" class="btn btn-success w-100 rounded-pill fw-bold text-white">
                    Ver Historial
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-premium p-4 h-100 shadow-sm d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-info-subtle text-info rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-pc-display fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-center">Mis Equipos</h5>
                    <p class="text-muted small text-center">Dispositivos y activos institucionales asignados a tu nombre.</p>
                    
                    <ul class="list-group list-group-flush mb-3 text-start">
                        @forelse(Auth::user()->equipos()->where('estado', true)->get() as $eq)
                            <li class="list-group-item bg-transparent border-0 px-0 py-2 d-flex justify-content-between align-items-center border-bottom border-light">
                                <div>
                                    <span class="fw-semibold d-block small text-truncate" style="max-width: 150px;">{{ $eq->nombre }}</span>
                                    @if($eq->numero_bien)
                                        <small class="text-secondary" style="font-size: 0.75rem;"><i class="bi bi-barcode me-1"></i>{{ $eq->numero_bien }}</small>
                                    @endif
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small">{{ $eq->tipoEquipo->nombre_tipo_equipo }}</span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent border-0 px-0 py-2 text-center text-muted small">
                                No tienes equipos asignados en este momento.
                            </li>
                        @endforelse
                    </ul>
                </div>
                <a href="{{ route('usuario.tickets.create') }}" class="btn btn-info w-100 rounded-pill fw-bold text-white mt-auto">
                    Reportar Fallo
                </a>
            </div>
        </div>
    </div>
</x-usuario-layout>