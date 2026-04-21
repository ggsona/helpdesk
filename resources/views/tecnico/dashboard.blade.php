@extends('layouts.admin')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold">Mi Panel Técnico</h3>
        <p class="text-muted">Gestión de tus tareas y tickets asignados.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-premium border-start border-primary border-4">
                <h6 class="text-muted small fw-bold">MIS TICKETS PENDIENTES</h6>
                <h2 class="fw-bold mb-0">5</h2>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card-premium border-start border-success border-4">
                <h6 class="text-muted small fw-bold">RESUELTOS ESTA SEMANA</h6>
                <h2 class="fw-bold mb-0">12</h2>
            </div>
        </div>
    </div>

    <div class="card-premium text-center py-5">
        <i class="bi bi-tools text-muted mb-3" style="font-size: 3rem;"></i>
        <h5>¡Bienvenido al área operativa!</h5>
        <p class="text-muted">Pronto verás aquí la lista rápida de tus incidencias activas.</p>
    </div>
@endsection