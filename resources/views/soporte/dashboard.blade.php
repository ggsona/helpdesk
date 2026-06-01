@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .theme-surface-soft {
        background-color: color-mix(in srgb, var(--bs-body-bg) 82%, var(--bs-tertiary-bg) 18%) !important;
    }
    [data-bs-theme="dark"] .theme-surface-soft {
        background-color: #1f2327 !important;
    }
    .theme-badge-soft {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border: 1px solid var(--bs-border-color) !important;
    }
</style>
@endpush
<div class="container-fluid">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold theme-text mb-0">
                @can('gestionar-roles')
                    Panel de Administración Global
                @elsecan('asignar-tickets')
                    Panel de Supervisión de Soporte
                @else
                    Mi Panel Técnico Operativo
                @endcan
            </h3>
            <p class="text-muted">Bienvenido, <strong class="text-primary">{{ Auth::user()->name }}</strong>. Aquí tienes el resumen de actividades de hoy.</p>
        </div>
        <div class="text-end">
            <span class="badge theme-badge-soft px-3 py-2 shadow-sm rounded-pill">
                <i class="bi bi-calendar3 me-2 text-primary"></i> {{ date('d/m/Y') }}
            </span>
        </div>
    </div>

    {{-- 1. SECCIÓN DE GESTORES Y COORDINADORES --}}
    @can('asignar-tickets')
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card-premium border-start border-danger border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Tickets Sin Asignar</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['nuevos'] ?? 0 }}</h2>
                    <div class="text-danger small mt-2 fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Requieren técnico</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-primary border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">En Gestión Activa</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['en_gestion'] ?? 0 }}</h2>
                    <div class="text-primary small mt-2 fw-semibold"><i class="bi bi-gear-fill me-1"></i> Especialistas trabajando</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-success border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Cerrados hoy</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['cerrados_hoy'] ?? 0 }}</h2>
                    <div class="text-success small mt-2 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Buen desempeño</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-warning border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Tiempo Promedio</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['tiempo_promedio'] ?? 'N/A' }}</h2>
                    <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-clock-history me-1"></i> Meta sugerida: 1h</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Rendimiento por Técnico</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center p-3 text-center theme-surface-soft border-secondary border-opacity-25 rounded-3" style="height: 250px;">
                        <canvas id="techChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Porcentaje por Categoría</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center p-3 text-center theme-surface-soft border-secondary border-opacity-25 rounded-3" style="height: 250px;">
                        <canvas id="catChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- 2. SECCIÓN DE TÉCNICOS (Solo si no es Gestor puro, o si tiene rol de técnico) --}}
    @cannot('asignar-tickets')
        @can('resolver-tickets')
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card-premium border-start border-warning border-4 shadow-sm">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Mis Casos Asignados</small>
                        <h2 class="fw-bold mb-0 theme-text mt-1">{{ ($stats['pendientes_tecnico'] ?? 0) + ($stats['en_proceso_tecnico'] ?? 0) }}</h2>
                        <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-tools me-1"></i> Pendientes y en progreso</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card-premium border-start border-success border-4 shadow-sm">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Resueltos Histórico</small>
                        <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['resueltos_tecnico'] ?? 0 }}</h2>
                        <div class="text-success small mt-2 fw-semibold"><i class="bi bi-check-lg me-1"></i> Casos cerrados con éxito</div>
                    </div>
                </div>
            </div>

            <div class="card-premium text-center py-5 shadow-sm border-0 mb-5">
                <i class="bi bi-laptop text-primary mb-3" style="font-size: 3.5rem;"></i>
                <h4 class="fw-bold theme-text mb-2">¡Hola de nuevo, Especialista!</h4>
                <p class="text-muted mx-auto" style="max-width: 500px;">
                    Accede a tu bandeja de **"Casos Asignados"** en el panel izquierdo para ver tus tareas prioritarias y comenzar a solucionar incidencias en vivo.
                </p>
                <a href="{{ route('soporte.tickets.tecnico.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold mt-3 shadow-sm">
                    <i class="bi bi-ticket-perforated me-2"></i>Ir a mis Casos
                </a>
            </div>
        @endcan
    @endcannot
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    @can('asignar-tickets')
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDark ? '#adb5bd' : '#6c757d';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // Chart Rendimiento Técnicos
        const ctxTech = document.getElementById('techChart').getContext('2d');
        new Chart(ctxTech, {
            type: 'bar',
            data: {
                labels: {!! $stats['chart_tech_labels'] ?? '[]' !!},
                datasets: [{
                    label: 'Tickets Resueltos',
                    data: {!! $stats['chart_tech_data'] ?? '[]' !!},
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                    x: { grid: { display: false }, ticks: { color: textColor } }
                }
            }
        });

        // Chart Categorías
        const ctxCat = document.getElementById('catChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: {!! $stats['chart_cat_labels'] ?? '[]' !!},
                datasets: [{
                    data: {!! $stats['chart_cat_data'] ?? '[]' !!},
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d'],
                    borderWidth: 2,
                    borderColor: isDark ? '#1a1c1e' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: textColor, boxWidth: 12, font: { size: 11 } } }
                },
                cutout: '70%'
            }
        });
    @endcan
});
</script>
@endpush
