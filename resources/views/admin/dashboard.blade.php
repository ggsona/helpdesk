@extends('layouts.admin')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold">Panel de Control</h3>
        <p class="text-muted">Resumen general de incidencias y soporte técnico.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-premium">
                <h6 class="text-muted small fw-bold">TICKETS ACTIVOS</h6>
                <h2 class="fw-bold mb-0 text-primary">24</h2>
            </div>
        </div>
        {{-- Puedes añadir más columnas aquí siguiendo el mismo estilo --}}
    </div>

    <div class="card-premium">
        <span class="fw-medium">
            <i class="bi bi-info-circle me-2 text-primary"></i>
            {{ __("You're logged in!") }}
        </span>
    </div>
@endsection