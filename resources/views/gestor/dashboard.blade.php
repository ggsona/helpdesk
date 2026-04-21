@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0">Panel de Supervisión</h3>
            <p class="text-muted">Bienvenido, Gestor. Aquí tienes el resumen operativo de hoy.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i> {{ date('d/m/Y') }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card-premium border-start border-primary border-4">
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Tickets Sin Asignar</small>
                <h2 class="fw-bold mb-0">12</h2> {{-- Aquí irá el conteo real --}}
                <div class="text-danger small mt-2"><i class="bi bi-exclamation-triangle"></i> Requieren atención</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium border-start border-info border-4">
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">En Proceso</small>
                <h2 class="fw-bold mb-0">25</h2>
                <div class="text-info small mt-2"><i class="bi bi-gear-fill"></i> Técnicos trabajando</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium border-start border-success border-4">
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Cerrados hoy</small>
                <h2 class="fw-bold mb-0">8</h2>
                <div class="text-success small mt-2"><i class="bi bi-check-circle"></i> Eficiencia del 85%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium border-start border-warning border-4">
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Promedio de Respuesta</small>
                <h2 class="fw-bold mb-0">45m</h2>
                <div class="text-warning small mt-2"><i class="bi bi-clock-history"></i> Tiempo meta: 1h</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-premium mb-4" style="min-height: 350px;">
                <h5 class="fw-bold mb-4">Rendimiento por Técnico</h5>
                <div class="d-flex align-items-center justify-content-center" style="height: 250px;">
                    <p class="text-muted italic">[ Aquí insertaremos el gráfico de barras de Chart.js ]</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-premium" style="min-height: 350px;">
                <h5 class="fw-bold mb-4">Tickets por Categoría</h5>
                <div class="d-flex align-items-center justify-content-center" style="height: 250px;">
                    <p class="text-muted italic">[ Gráfico de Pastel ]</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection