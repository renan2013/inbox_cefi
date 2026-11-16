@extends('layouts.app')

@section('title', 'Generador de Cortinas de Eventos - ' . \App\Services\ClienteService::nombre())

@section('styles')
<style>
    .generator-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 2rem;
    }

    .preview-card {
        background: #0b131b;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        color: #ffffff;
        overflow: hidden;
        position: relative;
    }

    .preview-frame {
        width: 100%;
        height: 480px;
        border: none;
        border-radius: 16px;
        background: #0b131b;
    }

    .chip-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        border-radius: 50px;
        padding: 5px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chip-btn:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
    }

    .chip-btn.active {
        background: #5fb230;
        border-color: #5fb230;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(95, 178, 48, 0.35);
    }

    .url-box {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px;
        font-family: monospace;
        font-size: 0.9rem;
        color: #0f172a;
        word-break: break-all;
    }
</style>
@endsection

@section('content')
@php
    $siglasInst = \App\Services\ClienteService::siglas();
    $nombreLegalInst = \App\Services\ClienteService::nombreLegal();
    $ciudadInst = \App\Services\ClienteService::all()['direccion'] ?? 'San José, Costa Rica';
@endphp

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-display me-1"></i> Módulo Audiovisual
                </span>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-link-45deg me-1"></i> Enlaces Públicos
                </span>
            </div>
            <h2 class="fw-bold text-dark mt-2 mb-0">Cortina Digital de Eventos & Promoción</h2>
            <p class="text-muted small mb-0">Personaliza la pantalla de bienvenida con cuenta regresiva, música de fondo y genera enlaces públicos para Zoom o proyectores.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('base_datos.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Base de Datos
            </a>
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" onclick="abrirCortinaNuevaPestana()">
                <i class="bi bi-play-circle-fill me-1"></i> Proyectar Cortina
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Formulario de Configuración -->
        <div class="col-lg-5">
            <div class="generator-card">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-sliders text-success me-2"></i> Configuración de la Cortina
                </h5>

                @if(isset($eventos) && $eventos->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Cargar desde Evento Existente (Opcional)</label>
                    <select class="form-select rounded-3" id="selectEventoDB" onchange="cargarEventoSeleccionado(this)">
                        <option value="">-- Personalizado / Escribir desde cero --</option>
                        @foreach($eventos as $ev)
                            <option value="{{ $ev->id }}"
                                    data-nombre="{{ $ev->nombre ?? '' }}"
                                    data-lugar="{{ $ev->lugar ?? '' }}">
                                📅 {{ $ev->nombre ?? 'Evento' }} ({{ $ev->lugar ?: $siglasInst }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Nombre del Evento -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Nombre del Evento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg rounded-3 fw-bold" id="inputTitulo"
                           value="Gran Charla Informativa de Matrícula"
                           placeholder="Ej: Open House {{ $siglasInst }} {{ date('Y') }}"
                           oninput="actualizarPreview()">
                    <div class="form-text">Reemplaza el nombre del curso tradicional por el nombre del evento.</div>
                </div>

                <!-- Subtítulo o Slogan -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Subtítulo / Lema / Sede</label>
                    <input type="text" class="form-control rounded-3" id="inputSubtitulo"
                           value="{{ $nombreLegalInst }} • Transmisión Oficial"
                           placeholder="Ej: Admisiones y Programas Académicos"
                           oninput="actualizarPreview()">
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <button type="button" class="chip-btn" onclick="aplicarPresetSubtitulo('Admisiones & Becas {{ $siglasInst }} • {{ $ciudadInst }}')">+ Admisiones</button>
                        <button type="button" class="chip-btn" onclick="aplicarPresetSubtitulo('Educación Continua y Programas Especializados')">+ Educación Continua</button>
                        <button type="button" class="chip-btn" onclick="aplicarPresetSubtitulo('Transmisión Especial en Vivo por Zoom')">+ Zoom en Vivo</button>
                        <button type="button" class="chip-btn" onclick="aplicarPresetSubtitulo('Programas Técnicos y Actualización Profesional')">+ Programas</button>
                    </div>
                </div>

                <!-- Expositor / Docente (Opcional) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Expositor / Docente (Opcional)</label>
                    <input type="text" class="form-control rounded-3" id="inputExpositor"
                           value=""
                           placeholder="Ej: Lic. Juan Pérez"
                           oninput="actualizarPreview()">
                </div>

                <!-- Minutos del Cronómetro -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Minutos de Cuenta Regresiva</label>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <input type="number" class="form-control rounded-3 text-center fw-bold fs-5" id="inputMinutos"
                               value="5" min="1" max="120" style="max-width: 110px;" oninput="actualizarPreview()">
                        <span class="fw-bold text-muted">minutos</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="chip-btn" id="chip3" onclick="setearMinutos(3)">3 min</button>
                        <button type="button" class="chip-btn active" id="chip5" onclick="setearMinutos(5)">5 min</button>
                        <button type="button" class="chip-btn" id="chip10" onclick="setearMinutos(10)">10 min</button>
                        <button type="button" class="chip-btn" id="chip15" onclick="setearMinutos(15)">15 min</button>
                        <button type="button" class="chip-btn" id="chip20" onclick="setearMinutos(20)">20 min</button>
                    </div>
                </div>

                <!-- Música de Fondo -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Música Ambiental de Fondo</label>
                    <select class="form-select rounded-3 mb-2" id="selectPista" onchange="actualizarPreview()">
                        <option value="ambient" selected>🎵 Ambiente Relajante (Predeterminada)</option>
                        <option value="piano">🎹 Piano Suave e Inspirador</option>
                        <option value="corporate">✨ Música Corporativa y Dinámica</option>
                        <option value="synth">⚡ Sintetizador Armónico Cósmico (100% Offline)</option>
                    </select>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="checkMusica" checked onchange="actualizarPreview()">
                        <label class="form-check-label small text-muted" for="checkMusica">Activar música por defecto al abrir</label>
                    </div>
                </div>

                <!-- Enlace Público Generado -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">
                        <i class="bi bi-link-45deg text-success"></i> Enlace Público Generado
                    </label>
                    <div class="url-box mb-2" id="urlBoxDisplay">Generando enlace...</div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success rounded-pill w-100 py-2 fw-bold" onclick="copiarEnlacePublico()">
                            <i class="bi bi-clipboard-check me-1"></i> Copiar Enlace Público
                        </button>
                        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2" onclick="compartirWhatsApp()" title="Compartir por WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Previsualizador en Vivo -->
        <div class="col-lg-7">
            <div class="preview-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="live-indicator-dot" style="width:10px;height:10px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                        <span class="small text-uppercase fw-bold text-white-50" style="letter-spacing:1px;">Simulador en Vivo</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="recargarPreview()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                        </button>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="abrirCortinaNuevaPestana()">
                            <i class="bi bi-arrows-fullscreen me-1"></i> Abrir a Pantalla Completa
                        </button>
                    </div>
                </div>

                <iframe id="previewFrame" class="preview-frame" src="about:blank"></iframe>
            </div>

            <!-- Consejos para Presentadores y Zoom -->
            <div class="card border-0 shadow-sm rounded-4 mt-3 bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i> ¿Cómo proyectar en Zoom o Videobeam?
                    </h6>
                    <ul class="text-muted small mb-0 ps-3">
                        <li><strong>Para Zoom:</strong> Abre la cortina, haz clic en <em>Compartir Pantalla</em> en Zoom, selecciona la pestaña de la cortina y asegúrate de marcar la casilla <strong>"Compartir audio"</strong> para que los asistentes escuchen la música.</li>
                        <li><strong>Para Videobeam / Proyector:</strong> Abre el enlace y pulsa la tecla <kbd>F11</kbd> o el botón inferior de pantalla completa.</li>
                        <li><strong>Controles en Vivo:</strong> Puedes pulsar la barra espaciadora para pausar el tiempo o ajustar <code>+1m</code> / <code>-1m</code> en pantalla.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const baseUrlPublica = "{{ route('cortina.evento') }}";
    const institucionLegal = @json($nombreLegalInst);

    function construirUrlCortina() {
        const params = new URLSearchParams({
            titulo: document.getElementById('inputTitulo').value.trim(),
            subtitulo: document.getElementById('inputSubtitulo').value.trim(),
            expositor: document.getElementById('inputExpositor').value.trim(),
            minutos: parseInt(document.getElementById('inputMinutos').value, 10) || 5,
            musica: document.getElementById('checkMusica').checked ? 1 : 0,
            pista: document.getElementById('selectPista').value
        });
        return `${baseUrlPublica}?${params.toString()}`;
    }

    let previewDebounce = null;
    function actualizarPreview() {
        const url = construirUrlCortina();
        document.getElementById('urlBoxDisplay').innerText = url;

        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(() => {
            document.getElementById('previewFrame').src = url;
        }, 300);
    }

    function recargarPreview() {
        document.getElementById('previewFrame').src = construirUrlCortina();
    }

    function setearMinutos(min) {
        document.getElementById('inputMinutos').value = min;
        document.querySelectorAll('.chip-btn').forEach(btn => btn.classList.remove('active'));
        const chip = document.getElementById('chip' + min);
        if (chip) chip.classList.add('active');
        actualizarPreview();
    }

    function aplicarPresetSubtitulo(texto) {
        document.getElementById('inputSubtitulo').value = texto;
        actualizarPreview();
    }

    function cargarEventoSeleccionado(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const nombre = opt.getAttribute('data-nombre') || '';
            const lugar = opt.getAttribute('data-lugar') || '';
            document.getElementById('inputTitulo').value = nombre;
            if (lugar) {
                document.getElementById('inputSubtitulo').value = `Sede: ${lugar} • ${institucionLegal}`;
            }
            actualizarPreview();
        }
    }

    function copiarEnlacePublico() {
        const url = construirUrlCortina();
        navigator.clipboard.writeText(url).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Enlace Copiado!',
                    text: 'El enlace público de la cortina está listo en tu portapapeles para compartir por Zoom, WhatsApp o Teams.',
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        }).catch(() => {
            prompt('Copia manualmente el enlace:', url);
        });
    }

    function compartirWhatsApp() {
        const url = construirUrlCortina();
        const titulo = document.getElementById('inputTitulo').value.trim();
        const mensaje = encodeURIComponent(`Hola! Aquí tienes el enlace de la cortina de bienvenida para *${titulo}*:\n${url}`);
        window.open(`https://api.whatsapp.com/send?text=${mensaje}`, '_blank');
    }

    function abrirCortinaNuevaPestana() {
        window.open(construirUrlCortina(), '_blank');
    }

    document.addEventListener('DOMContentLoaded', () => {
        actualizarPreview();
    });
</script>
@endsection
