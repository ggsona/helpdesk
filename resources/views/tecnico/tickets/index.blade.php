@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card-premium shadow-sm border-0">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-tools me-2"></i> Panel de Control Técnico
                    </h5>
                </div>

                <div class="card-body">
                    {{-- Contenedor de Pills estilo Gestor --}}
                    <div class="pills-container mb-4 shadow-sm py-2 px-4 rounded-3" style="background: var(--bg-main);">
                        <ul class="nav nav-pills nav-fill gap-2" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold rounded-3" id="pills-asignados-tab" data-bs-toggle="pill" data-bs-target="#pills-asignados" type="button" role="tab">
                                    <i class="bi bi-person-workspace me-2"></i> Mis Casos Asignados
                                    <span class="badge bg-warning text-black ms-2">
                                        {{ $ticketsCriticos->count() + $ticketsAltos->count() + $ticketsMedios->count() + $ticketsBajos->count() }}
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold rounded-3" id="pills-resueltos-tab" data-bs-toggle="pill" data-bs-target="#pills-resueltos" type="button" role="tab">
                                    <i class="bi bi-check-all me-2"></i> Historial de Resueltos
                                    <span class="badge bg-success text-white ms-2">{{ $ticketsResueltos->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        {{-- TAB: ASIGNADOS --}}
                        <div class="tab-pane fade show active" id="pills-asignados" role="tabpanel">
                            
                            @php $tieneTickets = false; @endphp

                            @foreach(['Critico' => [$ticketsCriticos, 'text-danger', '🔥'], 
                                      'Alta'    => [$ticketsAltos, 'text-warning', '⚡'], 
                                      'Media'   => [$ticketsMedios, 'text-info', '📋'], 
                                      'Baja'    => [$ticketsBajos, 'text-success', '🍃']] as $label => $info)
                                
                                @if($info[0]->count() > 0)
                                    @php $tieneTickets = true; @endphp
                                    <div class="mb-4">
                                        <h6 class="{{ $info[1] }} fw-bold mb-3">
                                            {{ $info[2] }} PRIORIDAD {{ strtoupper($label) }}
                                        </h6>
                                        @include('tecnico.tickets.partials.tabla', ['tickets' => $info[0]])
                                    </div>
                                @endif
                            @endforeach

                            @if(!$tieneTickets)
                                <div class="text-center py-5">
                                    <i class="bi bi-emoji-sunglasses fs-1 text-muted"></i>
                                    <p class="mt-3 text-custom-muted italic">¡Excelente! No tienes tickets pendientes por ahora.</p>
                                </div>
                            @endif
                        </div>

                        {{-- TAB: RESUELTOS --}}
                        <div class="tab-pane fade" id="pills-resueltos" role="tabpanel">
                            <h6 class="fw-bold text-success mb-3">✅ Casos Cerrados Recientemente</h6>
                            @include('tecnico.tickets.partials.tabla', ['tickets' => $ticketsResueltos, 'esResuelto' => true])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection