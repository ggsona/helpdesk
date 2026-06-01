<x-usuario-layout>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('usuario.tickets.index') }}" class="btn btn-link text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <h2 class="fw-bold mb-0 text-body">Ticket #{{ $ticket->id_ticket }}</h2>
                    <small class="">Creado el {{ $ticket->created_at->format('d/m/Y h:i A') }}</small>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->estatus == 0)
                        <span class="badge bg-secondary-subtle border border-secondary-subtle fs-6 px-3 shadow-sm">Borrador</span>
                        <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-send-check me-1"></i> Enviar a Soporte
                            </button>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 shadow-sm">Enviado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Información del Ticket --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3 text-body">Asunto: {{ $ticket->asunto }}</h5>
                        </div>
                        <p class=" border-start border-4 border-primary ps-3 py-2 bg-body rounded-end" style="white-space: pre-line;">{{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}</p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-paperclip me-2 text-primary"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card h-100 border border-light-subtle bg-body shadow-xs hover-shadow">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 140px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                <p class="small mb-0 text-uppercase text-secondary fw-bold">{{ $ext }}</p>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-transparent border-0 text-center py-2">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 border-0">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="mb-0 small"><i class="bi bi-info-circle me-1"></i> No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ELIMINADO EL CHAT INLINE: SE MOVIÓ A UN PANEL FLOTANTE (OFFCANVAS) ABAJO --}}
            </div>

            {{-- Sidebar derecho --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-body border-bottom border-light-subtle pb-2">Información de Seguimiento</h6>
                        
                        {{-- Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="small d-block mb-3">Técnico Responsable: </label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center p-3 bg-body border border-light-subtle rounded-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ strtoupper(substr($ticket->tecnico->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-body" style="font-size: 0.95rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-primary fw-medium">Agente de Soporte</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning bg-warning-subtle text-warning-emphasis border-0 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-clock-history fs-5 me-2"></i>
                                    Pendiente por asignar un técnico.
                                </div>
                            @endif
                        </div>

                        <hr class="text-secondary opacity-25">

                        <div class="mb-3">
                            <label class="small d-block">Categoría</label>
                            <span class="fw-bold text-body"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small d-block">Equipo afectado</label>
                            <span class="fw-bold text-body"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>

                        <div class="mb-0">
                            <label class="small d-block mb-1">Prioridad del Caso: </label>
                            @if($ticket->prioridad)
                                <span class="badge py-2 px-3 {{ $ticket->prioridad->nombre_prioridad == 'Alta' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} border border-light-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="small italic">Evaluando prioridad...</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card de Ayuda --}}
                <div class="card bg-primary bg-gradient text-white border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle display-6 mb-3 opacity-50"></i>
                        <h6>¿Necesitas ayuda inmediata?</h6>
                        <p class="small opacity-75">Si el problema es crítico, puedes comunicarte con la extensión de soporte técnico en tu oficina.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón Flotante para abrir el Chat --}}
    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#chatOffcanvas" 
            aria-controls="chatOffcanvas"
            style="position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px; z-index: 1040; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="bi bi-chat-dots-fill fs-3"></i>
        @php
            $mensajesPublicos = $ticket->comentarios->where('es_interno', false);
        @endphp
        @if($mensajesPublicos->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light border-2" style="font-size: 0.8rem; padding: 0.35em 0.65em;">
                {{ $mensajesPublicos->count() }}
            </span>
        @endif
    </button>

    {{-- Panel Deslizable (Offcanvas) de Chat Glassmorphism --}}
    <div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="chatOffcanvas" aria-labelledby="chatOffcanvasLabel" style="width: 450px; max-width: 100vw; background: rgba(var(--bs-body-bg-rgb), 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-10 py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold mb-0" id="chatOffcanvasLabel">Chat de Soporte</h5>
                    <small class="text-secondary d-flex align-items-center gap-1">
                        <span class="d-inline-block bg-success rounded-circle" style="width: 8px; height: 8px;"></span> Ticket #{{ $ticket->id_ticket }}
                    </small>
                </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body p-0 d-flex flex-column" style="background: rgba(var(--bs-secondary-bg-rgb), 0.3);">
            {{-- Área de Mensajes --}}
            <div class="chat-container flex-grow-1 p-4" id="offcanvasChatContainer" style="overflow-y: auto; scroll-behavior: smooth;">
                @if($mensajesPublicos->count() > 0)
                    @foreach($mensajesPublicos as $comentario)
                        <div class="d-flex flex-column mb-4 {{ $comentario->id_usuario == auth()->id() ? 'align-items-end' : 'align-items-start' }}">
                            <div class="d-flex align-items-end gap-2 {{ $comentario->id_usuario == auth()->id() ? 'flex-row-reverse' : '' }}">
                                {{-- Avatar Mini --}}
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white' : 'bg-body-secondary text-secondary-emphasis' }}" style="width: 35px; height: 35px; font-size: 0.8rem; flex-shrink: 0;">
                                    {{ substr($comentario->usuario->name, 0, 1) }}
                                </div>
                                
                                {{-- Burbuja de Chat --}}
                                <div class="p-3 rounded-4 shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white text-end' : 'bg-body border border-light-subtle' }}" style="max-width: 85%; border-bottom-{{ $comentario->id_usuario == auth()->id() ? 'right' : 'left' }}-radius: 4px !important;">
                                    <div class="d-flex align-items-center gap-2 mb-1 justify-content-{{ $comentario->id_usuario == auth()->id() ? 'end' : 'start' }}">
                                        <small class="fw-bold {{ $comentario->id_usuario == auth()->id() ? 'text-white text-opacity-75' : 'text-primary' }}" style="font-size: 0.75rem;">
                                            {{ $comentario->id_usuario == auth()->id() ? 'Tú' : $comentario->usuario->name }}
                                        </small>
                                        @if($comentario->id_usuario != auth()->id()) 
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.55rem; padding: 2px 6px;">SOPORTE</span> 
                                        @endif
                                    </div>
                                    <p class="mb-0" style="font-size: 0.95rem; line-height: 1.5; white-space: pre-wrap;">{{ $comentario->mensaje }}</p>
                                </div>
                            </div>
                            <small class="text-secondary mt-1 px-5" style="font-size: 0.65rem;">
                                {{ $comentario->created_at->format('h:i A') }}
                            </small>
                        </div>
                    @endforeach
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-5 opacity-50">
                        <i class="bi bi-chat-square-dots display-3 text-secondary mb-3"></i>
                        <h6 class="fw-bold">No hay mensajes</h6>
                        <p class="small text-secondary mb-0">Envía un mensaje para contactar al soporte técnico.</p>
                    </div>
                @endif
            </div>

            {{-- Área de Input --}}
            <div class="p-3 bg-body border-top border-secondary border-opacity-10 shadow-sm z-3">
                @if($ticket->estatus != 0)
                    <form action="{{ route('usuario.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="position-relative">
                            <textarea name="mensaje" 
                                      class="form-control form-control-premium bg-body-secondary bg-opacity-50 border-0 shadow-none pe-5" 
                                      placeholder="Escribe un mensaje..." 
                                      rows="1" 
                                      required 
                                      style="resize: none; border-radius: 25px; padding-top: 12px; padding-bottom: 12px; transition: all 0.3s ease;"></textarea>
                            <button class="btn btn-primary rounded-circle position-absolute end-0 top-50 translate-middle-y me-1 d-flex align-items-center justify-content-center shadow-sm hover-scale" 
                                    type="submit" 
                                    style="width: 38px; height: 38px;">
                                <i class="bi bi-send-fill fs-6 ms-1"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-secondary bg-opacity-10 text-secondary text-center py-3 rounded-4">
                        <span class="small fw-medium"><i class="bi bi-lock-fill me-1"></i> Envía el ticket para habilitar el chat.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación al hacer hover en el botón flotante
            const chatBtn = document.querySelector('[data-bs-target="#chatOffcanvas"]');
            if (chatBtn) {
                chatBtn.addEventListener('mouseenter', () => chatBtn.style.transform = 'scale(1.1) rotate(-5deg)');
                chatBtn.addEventListener('mouseleave', () => chatBtn.style.transform = 'scale(1) rotate(0)');
            }

            // Auto-scroll al fondo cuando se abre el offcanvas
            const chatOffcanvas = document.getElementById('chatOffcanvas');
            if (chatOffcanvas) {
                chatOffcanvas.addEventListener('shown.bs.offcanvas', function () {
                    const chatContainer = document.getElementById('offcanvasChatContainer');
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        // Pequeño focus visual a los mensajes
                        const mensajes = chatContainer.querySelectorAll('.d-flex.flex-column');
                        mensajes.forEach((msg, idx) => {
                            msg.style.opacity = '0';
                            msg.style.transform = 'translateY(10px)';
                            setTimeout(() => {
                                msg.style.transition = 'all 0.3s ease-out';
                                msg.style.opacity = '1';
                                msg.style.transform = 'translateY(0)';
                            }, 50 * idx);
                        });
                    }
                });
            }
        });
    </script>
    @endpush
</x-usuario-layout>