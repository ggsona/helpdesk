@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-1 fw-bold text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Rendimiento Técnico</h1>
            <p class="text-muted mb-0">Métricas clave, velocidades de respuesta y productividad global del equipo de soporte.</p>
        </div>
    </div>

    <!-- Tarjetas de KPI Globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-premium d-flex align-items-center p-3">
                <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-ticket-detailed fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Tickets Totales</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $globalTotal }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium d-flex align-items-center p-3">
                <div class="bg-success-subtle text-success rounded-3 p-3 me-3">
                    <i class="bi bi-check2-circle fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Resueltos/Cerrados</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $globalResueltos }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium d-flex align-items-center p-3">
                <div class="bg-warning-subtle text-warning rounded-3 p-3 me-3">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Activos/En Proceso</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $globalActivos }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium d-flex align-items-center p-3">
                <div class="bg-info-subtle text-info rounded-3 p-3 me-3">
                    <i class="bi bi-percent fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Tasa de Resolución</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $globalRate }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Rendimiento por Técnico -->
    <div class="card-premium mb-4">
        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-people-fill text-muted me-2"></i>Productividad de los Especialistas Técnicos</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Técnico</th>
                        <th class="border-0 text-center">Tickets Asignados</th>
                        <th class="border-0 text-center">Activos</th>
                        <th class="border-0 text-center">Resueltos</th>
                        <th class="border-0 text-center">Tasa de Cierre</th>
                        <th class="border-0 text-center">Tiempo Promedio de Cierre</th>
                        <th class="border-0">Productividad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics as $met)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" style="width: 42px; height: 42px; font-size: 1rem;">
                                        {{ substr($met['tecnico']->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark">{{ $met['tecnico']->name }}</span><br>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $met['tecnico']->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold text-dark">{{ $met['total'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                    {{ $met['activos'] }} activos
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                    {{ $met['resueltos'] }} resueltos
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark mb-1">{{ $met['rate'] }}%</div>
                                <div class="progress" style="height: 6px; border-radius: 10px; width: 100px; margin: 0 auto;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $met['rate'] }}%" aria-valuenow="{{ $met['rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($met['avg_speed'] !== null)
                                    <span class="fw-semibold text-dark"><i class="bi bi-clock-history text-muted me-1"></i>{{ $met['avg_speed'] }} hrs</span>
                                @else
                                    <span class="text-muted small">Sin datos</span>
                                @endif
                            </td>
                            <td>
                                @if($met['rate'] >= 80)
                                    <span class="badge bg-success text-white px-2.5 py-1.5 rounded fw-semibold" style="font-size: 0.75rem;">Excelente</span>
                                @elseif($met['rate'] >= 50)
                                    <span class="badge bg-info text-dark px-2.5 py-1.5 rounded fw-semibold" style="font-size: 0.75rem;">Eficiente</span>
                                @elseif($met['total'] > 0)
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded fw-semibold" style="font-size: 0.75rem;">En progreso</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded fw-semibold" style="font-size: 0.75rem;">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-people text-muted fs-2 mb-3 d-block"></i>
                                <p class="text-muted mb-0">No se encontraron técnicos registrados en el sistema.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
