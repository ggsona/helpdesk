@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4" style="color: var(--color-texto-principal);">🚀 Panel de Control Técnico</h2>

    <ul class="nav nav-pills mb-4 p-2 rounded-4 shadow-sm" id="pills-tab" role="tablist" style="background: var(--sb-bg); border: 1px solid var(--border-color);">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="pills-asignados-tab" data-bs-toggle="pill" data-bs-target="#pills-asignados" type="button" role="tab">
                <i class="bi bi-tools me-2"></i> Mis Casos Asignados
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="pills-resueltos-tab" data-bs-toggle="pill" data-bs-target="#pills-resueltos" type="button" role="tab">
                <i class="bi bi-check-all me-2"></i> Historial de Resueltos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-asignados" role="tabpanel">
            
            {{-- Sección: PRIORIDAD CRÍTICA --}}
            @if($ticketsCriticos->count() > 0)
                <div class="card-premium mb-4">
                    <h5 class="text-danger fw-bold border-start border-danger border-4 ps-3 mb-4">🔥 CRÍTICO (Atención Inmediata)</h5>
                    @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsCriticos])
                </div>
            @endif

            {{-- Sección: PRIORIDAD ALTA --}}
            @if($ticketsAltos->count() > 0)
                <div class="card-premium mb-4">
                    <h5 class="text-warning fw-bold border-start border-warning border-4 ps-3 mb-4">⚡ ALTA PRIORIDAD</h5>
                    @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsAltos])
                </div>
            @endif

            {{-- Sección: PRIORIDAD MEDIA --}}
            @if($ticketsMedios->count() > 0)
                <div class="card-premium mb-4">
                    <h5 class="text-info fw-bold border-start border-info border-4 ps-3 mb-4">📋 PRIORIDAD MEDIA</h5>
                    @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsMedios])
                </div>
            @endif

            {{-- Sección: PRIORIDAD BAJA --}}
            @if($ticketsBajos->count() > 0)
                <div class="card-premium mb-4">
                    <h5 class="text-success fw-bold border-start border-success border-4 ps-3 mb-4">🍃 PRIORIDAD BAJA</h5>
                    @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsBajos])
                </div>
            @endif

            {{-- En caso de que no haya absolutamente nada asignado --}}
            @if($ticketsCriticos->isEmpty() && $ticketsAltos->isEmpty() && $ticketsMedios->isEmpty() && $ticketsBajos->isEmpty())
                <div class="card-premium text-center py-5">
                    <i class="bi bi-emoji-sunglasses fs-1 text-muted"></i>
                    <p class="mt-3 mb-0">¡Excelente! No tienes tickets pendientes por ahora.</p>
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="pills-resueltos" role="tabpanel">
            <div class="card-premium">
                <h5 class="fw-bold mb-4" style="color: var(--color-texto-principal);">✅ Historial de Casos Cerrados</h5>
                @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsResueltos, 'esResuelto' => true])
            </div>
        </div>
    </div>
</div>
@endsection