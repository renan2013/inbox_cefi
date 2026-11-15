@extends('layouts.app')

@section('title', 'Cortina Digital de Eventos & Promoción - Inbox 2.0')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold mb-1">
                <i class="bi bi-display me-1"></i>Módulo Audiovisual
            </span>
            <h2 class="fw-bold mb-0"><i class="bi bi-display-fill text-primary me-2"></i>Cortina Digital de Eventos & Clases en Vivo</h2>
            <p class="text-muted small mb-0">Personalice la pantalla de bienvenida con cuenta regresiva para proyectar en Zoom, auditorios o eventos institucionales.</p>
        </div>
        <a href="{{ route('base_datos.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-gift me-1"></i>Ir a Eventos & Sorteos
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-sliders text-info me-2"></i>Configurar Pantalla de Proyección</h5>

                    @if($eventos->count() > 0)
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Cargar datos desde un Evento registrado (Opcional)</label>
                        <select class="form-select form-select-sm" onchange="if(this.value) { document.getElementById('c_titulo').value = this.value; actualizarPreviewCortina(); }">
                            <option value="">-- Seleccionar evento --</option>
                            @foreach($eventos as $ev)
                                <option value="{{ $ev->nombre }}">{{ $ev->nombre }} ({{ $ev->codigo }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título Principal del Evento / Sesión</label>
                        <input type="text" id="c_titulo" class="form-control" value="{{ config('cliente.nombre_legal', 'CENTRO DE ENSEÑANZA DE FORMACIÓN INTEGRAL') }}" oninput="actualizarPreviewCortina()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Subtítulo / Tema</label>
                        <input type="text" id="c_subtitulo" class="form-control" value="Bienvenidos — Iniciamos en breves minutos" oninput="actualizarPreviewCortina()">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Expositor / Docente (Opcional)</label>
                            <input type="text" id="c_expositor" class="form-control" placeholder="Ej: Lic. Juan Pérez" oninput="actualizarPreviewCortina()">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Cuenta Regresiva (min)</label>
                            <input type="number" id="c_minutos" class="form-control" value="5" min="1" max="120" onchange="actualizarPreviewCortina()">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a id="btnAbrirCortina" href="{{ route('cortina.evento') }}" target="_blank" class="btn btn-primary btn-lg rounded-pill fw-bold shadow">
                            <i class="bi bi-arrows-fullscreen me-2"></i>Abrir Cortina en Pantalla Completa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #090d16;">
                <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center text-white">
                    <span class="small fw-bold"><i class="bi bi-eye text-success me-1"></i>Vista Previa en Tiempo Real</span>
                    <span class="badge bg-dark text-white-50 font-monospace">1920x1080 HD Ready</span>
                </div>
                <iframe id="cortinaPreviewFrame" src="{{ route('cortina.evento') }}" style="width:100%; height:460px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const baseCortinaUrl = "{{ route('cortina.evento') }}";
function actualizarPreviewCortina() {
    const params = new URLSearchParams({
        titulo: document.getElementById('c_titulo').value,
        subtitulo: document.getElementById('c_subtitulo').value,
        expositor: document.getElementById('c_expositor').value,
        minutos: document.getElementById('c_minutos').value
    });
    const fullUrl = baseCortinaUrl + '?' + params.toString();
    document.getElementById('cortinaPreviewFrame').src = fullUrl;
    document.getElementById('btnAbrirCortina').href = fullUrl;
}
</script>
@endsection
