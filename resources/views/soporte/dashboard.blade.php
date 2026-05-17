@extends('layouts.admin')

@section('content')
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
            <span class="badge bg-light text-dark border px-3 py-2 shadow-sm rounded-pill">
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
                    <h2 class="fw-bold mb-0 theme-text mt-1">12</h2>
                    <div class="text-danger small mt-2 fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Requieren técnico</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-primary border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">En Gestión Activa</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">25</h2>
                    <div class="text-primary small mt-2 fw-semibold"><i class="bi bi-gear-fill me-1"></i> Especialistas trabajando</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-success border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Cerrados hoy</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">8</h2>
                    <div class="text-success small mt-2 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Eficiencia del 85%</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-warning border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Tiempo Promedio</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">45m</h2>
                    <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-clock-history me-1"></i> Tiempo meta: 1h</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Rendimiento por Técnico</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center border border-dashed rounded-3 p-5 text-center bg-light theme-bg-dark border-secondary border-opacity-25" style="height: 250px;">
                        <i class="bi bi-activity text-primary fs-1 mb-2"></i>
                        <p class="text-muted mb-0 small">[ Gráfica en tiempo real de carga de trabajo de especialistas ]</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Porcentaje por Categoría</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center border border-dashed rounded-3 p-5 text-center bg-light theme-bg-dark border-secondary border-opacity-25" style="height: 250px;">
                        <i class="bi bi-pie-chart text-primary fs-1 mb-2"></i>
                        <p class="text-muted mb-0 small">[ Hardware (55%) | Software (30%) | Redes (15%) ]</p>
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
                        <h2 class="fw-bold mb-0 theme-text mt-1">5</h2>
                        <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-tools me-1"></i> Pendientes de resolución</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card-premium border-start border-success border-4 shadow-sm">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Resueltos esta semana</small>
                        <h2 class="fw-bold mb-0 theme-text mt-1">12</h2>
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
