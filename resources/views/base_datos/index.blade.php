@extends('layouts.app')

@section('title', 'Base de Datos, Eventos & Sorteos en Vivo - Inbox 2.0')

@section('content')
<div class="container-fluid py-4">
    <!-- Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-gift-fill me-1"></i>Captación & Eventos en Vivo
                </span>
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">
                    {{ $eventos->count() }} eventos | {{ $prospectos->count() }} contactos
                </span>
            </div>
            <h2 class="fw-bold mb-0"><i class="bi bi-gift-fill text-warning me-2"></i>Base de Datos, Eventos QR & Sorteos en Vivo</h2>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearEvento">
                <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Evento con Sorteo QR
            </button>
            <a href="{{ route('cortina.index') }}" class="btn btn-outline-info rounded-pill px-3 fw-semibold">
                <i class="bi bi-display-fill me-1"></i>Cortina Digital
            </a>
            <a href="{{ route('base_datos.exportar') }}" class="btn btn-outline-success rounded-pill px-3 fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i>Descargar Base Completa (CSV)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Pestañas: Eventos & Sorteos | Directorio de Contactos -->
    <ul class="nav nav-pills gap-2 mb-4" id="bdTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="tab-eventos-btn" data-bs-toggle="pill" data-bs-target="#tabEventos" type="button" role="tab">
                <i class="bi bi-collection-play-fill me-1"></i>Eventos & Sorteos en Vivo ({{ $eventos->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="tab-prospectos-btn" data-bs-toggle="pill" data-bs-target="#tabProspectos" type="button" role="tab">
                <i class="bi bi-telephone-fill me-1"></i>Directorio de Contactos ({{ $prospectos->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: EVENTOS Y SORTEOS -->
        <div class="tab-pane fade show active" id="tabEventos" role="tabpanel">
            <div class="row g-4">
                @forelse($eventos as $ev)
                    @php
                        $urlRegistro = route('evento.registro_publico', $ev->codigo);
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($urlRegistro);
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100" style="background: var(--card-dark);">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary font-monospace px-3 py-1 rounded-pill">
                                            {{ $ev->codigo }}
                                        </span>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                                            <i class="bi bi-people-fill me-1"></i>{{ $ev->total_participantes }} inscritos
                                        </span>
                                    </div>
                                    <h5 class="fw-bold mb-1">{{ $ev->nombre }}</h5>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $ev->lugar ?: 'Virtual / Presencial' }} |
                                        <i class="bi bi-calendar-event ms-1 me-1"></i>{{ $ev->fecha_evento ? date('d/m/Y', strtotime($ev->fecha_evento)) : 'Hoy' }}
                                    </p>
                                    <div class="p-2 px-3 rounded-3 mb-3" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25);">
                                        <small class="text-warning fw-bold d-block"><i class="bi bi-trophy-fill me-1"></i>Premio a Sortear:</small>
                                        <span class="fw-bold">{{ $ev->premio }}</span>
                                    </div>

                                    @if(!empty($ev->ganador_nombre))
                                        <div class="alert alert-success py-2 px-3 rounded-3 small mb-3">
                                            <i class="bi bi-award-fill me-1"></i><strong>Ganador Oficial:</strong> {{ $ev->ganador_nombre }} ({{ $ev->ganador_telefono }})
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-secondary border-opacity-25">
                                    <a href="{{ route('base_datos.sorteo', $ev->id) }}" target="_blank" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 flex-grow-1">
                                        <i class="bi bi-stars me-1"></i>Abrir Ruleta / Sorteo en Vivo
                                    </a>
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3"
                                            onclick="mostrarQrEvento('{{ addslashes($ev->nombre) }}', '{{ $urlRegistro }}', '{{ $qrUrl }}')">
                                        <i class="bi bi-qr-code me-1"></i>QR Registro
                                    </button>
                                    <form method="POST" action="{{ route('base_datos.evento.destroy', $ev->id) }}" onsubmit="return confirm('¿Eliminar este evento?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center" style="background: var(--card-dark);">
                            <i class="bi bi-gift fs-1 text-warning mb-2"></i>
                            <h5 class="fw-bold">Aún no hay eventos de captación creados</h5>
                            <p class="text-muted small mb-3">Cree un evento para proyectar el código QR en vivo, capturar prospectos desde el celular y realizar sorteos en pantalla completa.</p>
                            <div>
                                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearEvento">
                                    <i class="bi bi-plus-circle me-1"></i>Crear Primer Evento
                                </button>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: DIRECTORIO DE CONTACTOS -->
        <div class="tab-pane fade" id="tabProspectos" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-info me-2"></i>Directorio Consolidado de Contactos y Prospectos</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('marketing.prospectos.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i>Importar Masivo / Gestionar
                            </a>
                            <a href="{{ route('whatsapp.campanas.index') }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                <i class="bi bi-whatsapp me-1"></i>Lanzar Campaña WhatsApp
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Teléfono / WhatsApp</th>
                                    <th>Correo</th>
                                    <th>Interés Académico</th>
                                    <th>Origen / Evento</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prospectos as $p)
                                    <tr>
                                        <td class="fw-bold">{{ $p->nombre }} {{ $p->apellidos }}</td>
                                        <td>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->telefono) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                                                <i class="bi bi-whatsapp me-1"></i>{{ $p->telefono }}
                                            </a>
                                        </td>
                                        <td class="small text-muted">{{ $p->email ?: '—' }}</td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ $p->interes ?: 'General' }}</span></td>
                                        <td><span class="badge bg-secondary bg-opacity-25">{{ $p->origen }}</span></td>
                                        <td class="small text-muted">{{ $p->created_at ? date('d/m/Y H:i', strtotime($p->created_at)) : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No hay contactos registrados todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR EVENTO -->
<div class="modal fade" id="modalCrearEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('base_datos.evento.store') }}" class="modal-content border-0 rounded-4 shadow-lg" style="background: var(--card-dark);">
            @csrf
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-gift-fill text-warning me-2"></i>Crear Nuevo Evento de Captación & Sorteo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Nombre del Evento / Feria / Webinar *</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Feria Vocacional 2026 / Open House" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Código Corto (Opcional)</label>
                        <input type="text" name="codigo" class="form-control font-monospace" placeholder="Ej: FERIA-2026">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Fecha del Evento</label>
                        <input type="date" name="fecha_evento" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Lugar / Modalidad</label>
                        <input type="text" name="lugar" class="form-control" placeholder="Ej: Auditorio Principal / Zoom Live">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-warning"><i class="bi bi-trophy-fill me-1"></i>Premio a Sortear en Vivo *</label>
                        <input type="text" name="premio" class="form-control" value="1 Beca de Matrícula 100% Gratuita" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="bi bi-check-lg me-1"></i>Crear Evento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL QR DEL EVENTO -->
<div class="modal fade" id="modalQrEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-center">
        <div class="modal-content border-0 rounded-4 shadow-lg p-4" style="background: var(--card-dark);">
            <h5 class="fw-bold mb-1" id="qrModalTitle">Código QR de Registro en Vivo</h5>
            <p class="text-muted small mb-3">Proyecte este código QR para que los asistentes se registren desde su teléfono:</p>
            <div class="bg-white p-3 rounded-4 d-inline-block mx-auto mb-3 shadow">
                <img id="qrModalImg" src="" alt="QR Evento" style="width: 240px; height: 240px;">
            </div>
            <div class="input-group input-group-sm mb-3">
                <input type="text" id="qrModalUrl" class="form-control font-monospace text-center" readonly>
                <button class="btn btn-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('qrModalUrl').value); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Enlace copiado',showConfirmButton:false,timer:1200});">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
            </div>
            <a id="qrModalOpenBtn" href="#" target="_blank" class="btn btn-outline-info rounded-pill fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Formulario Público
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#tabProspectos') {
        const btn = document.getElementById('tab-prospectos-btn');
        if (btn) btn.click();
    }
});

function mostrarQrEvento(nombre, url, qrImg) {
    document.getElementById('qrModalTitle').textContent = nombre;
    document.getElementById('qrModalImg').src = qrImg;
    document.getElementById('qrModalUrl').value = url;
    document.getElementById('qrModalOpenBtn').href = url;
    new bootstrap.Modal(document.getElementById('modalQrEvento')).show();
}
</script>
@endsection
