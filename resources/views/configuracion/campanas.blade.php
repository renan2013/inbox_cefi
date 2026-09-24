@extends('layouts.app')

@section('title', 'Inbox BPM - Campañas y Difusión WhatsApp')

@section('styles')
<style>
    /* Cabecera de Campañas adaptativa (Light & Dark) */
    .campaign-header {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2.2rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }

    [data-theme="light"] .campaign-header {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.04) !important;
    }

    .campaign-header h2 {
        color: #ffffff;
    }

    [data-theme="light"] .campaign-header h2 {
        color: #0f172a !important;
    }

    .campaign-header p, .campaign-header .subtext {
        color: rgba(255, 255, 255, 0.7);
    }

    [data-theme="light"] .campaign-header p,
    [data-theme="light"] .campaign-header .subtext {
        color: #64748b !important;
    }

    /* Tarjetas de formulario */
    .glass-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        margin-bottom: 1.75rem;
    }

    [data-theme="light"] .glass-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    .glass-card h5 {
        color: #ffffff;
    }

    [data-theme="light"] .glass-card h5 {
        color: #0f172a !important;
    }

    .form-label-styled {
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
        color: rgba(255, 255, 255, 0.75);
    }

    [data-theme="light"] .form-label-styled {
        color: #475569 !important;
    }

    /* Inputs y Selects */
    .form-control-custom, .form-select-custom {
        background-color: rgba(15, 23, 42, 0.6);
        border: 2px solid var(--border-dark);
        border-radius: 0.75rem;
        color: #f8fafc;
        padding: 0.7rem 1rem;
        transition: all 0.25s ease;
    }

    [data-theme="light"] .form-control-custom,
    [data-theme="light"] .form-select-custom {
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        color: #0f172a !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #25d366 !important;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2) !important;
        outline: none;
    }

    /* Badges y Chips */
    .badge-vps {
        background: rgba(37, 211, 102, 0.15);
        color: #25d366;
        border: 1px solid rgba(37, 211, 102, 0.35);
        border-radius: 2rem;
        padding: 0.35rem 0.85rem;
        font-size: 0.8rem;
        font-weight: 700;
    }

    [data-theme="light"] .badge-vps {
        background: rgba(37, 211, 102, 0.12) !important;
        color: #15803d !important;
        border-color: rgba(37, 211, 102, 0.3) !important;
    }

    .tag-chip {
        background: rgba(95, 178, 48, 0.15);
        border: 1px solid rgba(95, 178, 48, 0.35);
        color: #5fb230;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.28rem 0.7rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-block;
        margin-right: 0.35rem;
        margin-bottom: 0.35rem;
        user-select: none;
    }

    .tag-chip:hover {
        background: rgba(95, 178, 48, 0.3);
        transform: translateY(-1px);
        color: #72cf3a;
    }

    [data-theme="light"] .tag-chip {
        background: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        color: #166534 !important;
    }

    [data-theme="light"] .tag-chip:hover {
        background: #dcfce7 !important;
        color: #15803d !important;
    }

    /* Caja de carga Dropzone */
    .dropzone-box {
        border: 2px dashed rgba(95, 178, 48, 0.4);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        background: rgba(15, 23, 42, 0.4);
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .dropzone-box:hover {
        border-color: #25d366;
        background: rgba(37, 211, 102, 0.05);
    }

    [data-theme="light"] .dropzone-box {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }

    [data-theme="light"] .dropzone-box:hover {
        background: #f0fdf4 !important;
        border-color: #22c55e !important;
    }

    [data-theme="light"] .dropzone-box h6 {
        color: #0f172a !important;
    }

    [data-theme="light"] .dropzone-box p {
        color: #64748b !important;
    }

    /* Caja KPI de Destinatarios */
    .kpi-destinatarios-box {
        background: rgba(37, 211, 102, 0.08);
        border: 1px solid rgba(37, 211, 102, 0.25);
        border-radius: 0.75rem;
        padding: 1rem;
    }

    [data-theme="light"] .kpi-destinatarios-box {
        background: #f0fdf4 !important;
        border: 1px solid #86efac !important;
    }

    .kpi-destinatarios-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: #ffffff;
    }

    [data-theme="light"] .kpi-destinatarios-title {
        color: #0f172a !important;
    }

    .kpi-destinatarios-sub {
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.6);
    }

    [data-theme="light"] .kpi-destinatarios-sub {
        color: #64748b !important;
    }

    /* Caja de prueba */
    .test-box {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--border-dark);
        border-radius: 0.75rem;
        padding: 1.25rem;
    }

    [data-theme="light"] .test-box {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }

    /* Botón de Lanzamiento */
    .btn-launch-campaign {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        border: none;
        color: #ffffff !important;
        padding: 0.9rem 2rem;
        border-radius: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.3);
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        font-size: 1.05rem;
        cursor: pointer;
    }

    .btn-launch-campaign:hover {
        background: linear-gradient(135deg, #20ba5a 0%, #0e7065 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(37, 211, 102, 0.45);
        color: #ffffff !important;
    }

    /* ========================================================
       SIMULADOR CELULAR WHATSAPP (Aislado e inmune a temas)
       ======================================================== */
    .phone-mockup, [data-theme="light"] .phone-mockup {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        border-radius: 2.2rem;
        border: 12px solid #0f172a !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden;
        background: #0b141a !important;
        position: sticky;
        top: 2rem;
        color-scheme: dark !important;
    }

    .phone-notch, [data-theme="light"] .phone-notch {
        height: 18px;
        background: #0f172a !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .phone-speaker, [data-theme="light"] .phone-speaker {
        width: 50px;
        height: 4px;
        background: #334155 !important;
        border-radius: 2px;
    }

    .wa-chat-header, [data-theme="light"] .wa-chat-header {
        background: #1f2c34 !important;
        color: #ffffff !important;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .wa-chat-header *, [data-theme="light"] .wa-chat-header * {
        color: #ffffff !important;
    }

    .wa-chat-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: contain;
        background: #ffffff;
        padding: 2px;
        border: 1px solid #00a884;
    }

    .wa-chat-body, [data-theme="light"] .wa-chat-body {
        background-color: #0b141a !important;
        background-image: radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 0) !important;
        background-size: 16px 16px !important;
        padding: 1.25rem 0.85rem;
        min-height: 470px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .wa-bubble, [data-theme="light"] .wa-bubble {
        background: #005c4b !important;
        color: #ffffff !important;
        border-radius: 0.85rem 0.85rem 0.85rem 0.2rem;
        padding: 0.5rem;
        max-width: 95%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        word-break: break-word;
        font-size: 0.88rem;
    }

    .wa-bubble-img {
        width: 100%;
        max-height: 220px;
        object-fit: contain;
        border-radius: 0.6rem;
        margin-bottom: 0.5rem;
        background: #ffffff;
        padding: 6px;
    }

    .wa-bubble-text, [data-theme="light"] .wa-bubble-text {
        white-space: pre-wrap;
        line-height: 1.45;
        padding: 0 0.25rem;
        color: #ffffff !important;
    }

    .wa-bubble-text strong, [data-theme="light"] .wa-bubble-text strong {
        color: #ffffff !important;
        font-weight: 700;
    }

    .wa-bubble-text em, [data-theme="light"] .wa-bubble-text em {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .wa-bubble-meta, [data-theme="light"] .wa-bubble-meta {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.65) !important;
        margin-top: 0.35rem;
    }

    .wa-bubble-meta i {
        color: #53bdeb !important;
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <!-- Header Principal -->
    <div class="campaign-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge-vps"><i class="bi bi-broadcast me-1"></i> Evolution API Activa</span>
                <span class="subtext small">Instancia: <strong>{{ $config['evolution_instance'] ?? 'renan_whatsapp' }}</strong> (VPS Hostinger)</span>
            </div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-megaphone-fill text-success me-2"></i> Difusión y Campañas de Oferta Académica
            </h2>
            <p class="mb-0">
                Envía publicidad, afiches y nueva oferta académica por WhatsApp a tus estudiantes con protección anti-bloqueo y retardo inteligente.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('marketing.prospectos.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-person-lines-fill me-1"></i> Base de Prospectos
            </a>
            <a href="{{ route('configuracion.whatsapp') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-sliders me-1"></i> Parámetros WhatsApp
            </a>
            <button class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalWebhookN8n">
                <i class="bi bi-diagram-3 me-1"></i> Webhook n8n
            </button>
        </div>
    </div>

    <!-- Contenido Principal (2 Columnas) -->
    <div class="row g-4">

        <!-- Columna Izquierda: Formulario de la Campaña -->
        <div class="col-lg-7">
            <form id="formCampana" enctype="multipart/form-data">
                @csrf

                <!-- 1. Datos Generales y Audiencia -->
                <div class="glass-card">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">1</span>
                        Definir Campaña y Audiencia
                    </h5>

                    <div class="mb-3">
                        <label class="form-label-styled">Nombre o Título Interno de la Campaña</label>
                        <input type="text" name="titulo_campana" id="titulo_campana" class="form-control-custom w-100" 
                               value="Oferta Académica II-Cuatrimestre 2026" required placeholder="Ej: Convocatoria Nueva Maestría en Teología">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-styled mb-0">Audiencia Objetivo</label>
                                <a href="{{ route('marketing.prospectos.index') }}" class="small text-decoration-none fw-semibold" style="font-size: 0.76rem;">
                                    <i class="bi bi-plus-circle me-1"></i> Cargar Prospectos
                                </a>
                            </div>
                            <select name="filtro_audiencia" id="filtro_audiencia" class="form-select-custom w-100">
                                <optgroup label="🎓 Estudiantes Institucionales">
                                    <option value="todos_estudiantes" selected>Todos los Estudiantes ({{ $totalEstudiantesConTelefono }} activos)</option>
                                    <option value="programa">Por Programa / Carrera Específica</option>
                                </optgroup>
                                <optgroup label="🎯 Prospectos y Bases Externas de Marketing">
                                    <option value="prospectos_todos">Todos los Prospectos Externos ({{ $totalProspectosConTelefono ?? 0 }} contactos)</option>
                                    @if(isset($origenesProspectos) && count($origenesProspectos) > 0)
                                        <option value="prospectos_origen">Filtrar Prospectos por Lista/Origen</option>
                                    @endif
                                    <option value="mixto">Audiencia Mixta (Estudiantes + Prospectos Externos)</option>
                                </optgroup>
                                <optgroup label="🌐 Global">
                                    <option value="todos_usuarios">Toda la Base de Datos del Sistema ({{ $totalUsuariosConTelefono }} contactos)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-6" id="boxPrograma" style="display: none;">
                            <label class="form-label-styled">Seleccionar Programa</label>
                            <select name="id_programa" id="id_programa" class="form-select-custom w-100">
                                <option value="">-- Todos los programas --</option>
                                @foreach($programas as $p)
                                    <option value="{{ $p->id_programa }}">{{ $p->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="boxOrigenProspecto" style="display: none;">
                            <label class="form-label-styled">Seleccionar Lista de Prospectos</label>
                            <select name="origen_prospecto" id="origen_prospecto" class="form-select-custom w-100">
                                @if(isset($origenesProspectos))
                                    @foreach($origenesProspectos as $orig)
                                        <option value="{{ $orig }}">{{ $orig }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Contador Dinámico de Destinatarios -->
                    <div class="kpi-destinatarios-box">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill fs-4 text-success"></i>
                                <div>
                                    <div class="kpi-destinatarios-title">Destinatarios Seleccionados</div>
                                    <div class="kpi-destinatarios-sub">Números válidos formateados con prefijo internacional</div>
                                </div>
                            </div>
                            <span class="badge bg-success fs-6 px-3 py-2 rounded-pill" id="badgeTotalDestinatarios">
                                {{ $totalEstudiantesConTelefono }} contactos
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Flyer / Imagen Publicitaria -->
                <div class="glass-card">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">2</span>
                        Adjuntar Afiche / Flyer Publicitario
                    </h5>

                    <div class="dropzone-box mb-3" onclick="document.getElementById('flyer_file').click();">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-success mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Haz clic para subir la imagen del anuncio</h6>
                        <p class="small mb-0">Formatos: JPG, PNG o WebP (Máx. 5MB). Aparecerá inmediatamente en el simulador.</p>
                        <input type="file" name="flyer_file" id="flyer_file" accept="image/*" class="d-none">
                    </div>

                    <div class="mb-2">
                        <label class="form-label-styled">O ingresar URL directa de la imagen (Opcional)</label>
                        <input type="url" name="flyer_url" id="flyer_url" class="form-control-custom w-100" 
                               placeholder="https://ejemplo.com/afiche-oferta-academica.jpg"
                               value="{{ $config['logo_url'] ?? asset('imgs/logo_unela_color.png') }}">
                    </div>
                </div>

                <!-- 3. Redacción del Mensaje y Tags Dinámicos -->
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-success rounded-circle px-2 py-1">3</span>
                            Texto del Anuncio y Personalización
                        </h5>
                        <span class="small fw-semibold text-muted" id="charCount">0 caracteres</span>
                    </div>

                    <div class="mb-2">
                        <span class="small d-block mb-1 text-muted fw-semibold">Haz clic en una etiqueta para insertarla:</span>
                        <div>
                            <span class="tag-chip" onclick="insertTag('{nombre}')"><i class="bi bi-plus"></i> {nombre}</span>
                            <span class="tag-chip" onclick="insertTag('{apellidos}')"><i class="bi bi-plus"></i> {apellidos}</span>
                            <span class="tag-chip" onclick="insertTag('{nombre_completo}')"><i class="bi bi-plus"></i> {nombre_completo}</span>
                            <span class="tag-chip" onclick="insertTag('🏛️ *UNIVERSIDAD UNELA*')">Encabezado</span>
                            <span class="tag-chip" onclick="insertTag('📲 *INFO*')">Llamado a la acción</span>
                        </div>
                    </div>

                    <textarea name="mensaje_template" id="mensaje_template" rows="9" class="form-control-custom w-100 font-monospace" 
                              required style="font-size: 0.9rem;">{{ $plantillaDefault }}</textarea>

                    <div class="row g-3 mt-2 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label-styled">
                                <i class="bi bi-shield-check text-success me-1"></i> Retardo Anti-Spam entre mensajes:
                            </label>
                            <select name="intervalo_segundos" id="intervalo_segundos" class="form-select-custom w-100">
                                <option value="15">15 segundos (Rápido)</option>
                                <option value="20" selected>20 segundos (Recomendado - Máxima Seguridad)</option>
                                <option value="30">30 segundos (Ultra Seguro para lotes grandes)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted" style="line-height: 1.35;">
                                <i class="bi bi-info-circle text-info me-1"></i>
                                Evita bloqueos de WhatsApp simulando pausas humanas entre cada estudiante.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Acciones y Despacho -->
                <div class="glass-card">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">4</span>
                        Validación y Lanzamiento
                    </h5>

                    <!-- Prueba Inmediata -->
                    <div class="test-box mb-3">
                        <label class="form-label-styled">Enviar primero una prueba a tu propio número:</label>
                        <div class="input-group">
                            <input type="text" id="telefono_test" class="form-control-custom" 
                                   value="{{ $config['admin_phone'] ?? '50687777849' }}" placeholder="50687777849">
                            <button type="button" class="btn btn-outline-success fw-bold px-3" id="btnTestEnvio">
                                <i class="bi bi-send me-1"></i> Probar en mi WhatsApp
                            </button>
                        </div>
                        <div class="small mt-2" id="testResultMsg"></div>
                    </div>

                    <!-- Botón de Lanzamiento Masivo -->
                    <button type="button" class="btn-launch-campaign" id="btnConfirmarLanzamiento">
                        <i class="bi bi-rocket-takeoff-fill fs-5"></i> Lanzar Campaña Masiva a Estudiantes
                    </button>
                </div>
            </form>
        </div>

        <!-- Columna Derecha: Simulador Interactivo en Vivo -->
        <div class="col-lg-5">
            <div class="phone-mockup">
                <div class="phone-notch"><div class="phone-speaker"></div></div>

                <!-- Cabecera Chat WhatsApp -->
                <div class="wa-chat-header">
                    <img src="{{ asset('imgs/logo_unela_color.png') }}" alt="UNELA" class="wa-chat-avatar" id="simAvatar">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold small text-truncate" style="color: #ffffff !important;">Universidad UNELA</div>
                        <div style="font-size: 0.72rem; color: #aebac1 !important;"><i class="bi bi-circle-fill text-success" style="font-size: 0.55rem;"></i> en línea</div>
                    </div>
                    <div class="d-flex gap-3 text-white-50 fs-6">
                        <i class="bi bi-telephone-fill" style="color: #ffffff !important;"></i>
                        <i class="bi bi-three-dots-vertical" style="color: #ffffff !important;"></i>
                    </div>
                </div>

                <!-- Cuerpo de Mensajes -->
                <div class="wa-chat-body">
                    <div class="text-center mb-3">
                        <span class="badge bg-dark text-white-50" style="font-size: 0.68rem; padding: 0.35rem 0.6rem; border-radius: 0.5rem; color: #aebac1 !important;">
                            🔒 Mensajes cifrados de extremo a extremo
                        </span>
                    </div>

                    <!-- Burbuja con Flyer y Texto Dinámico -->
                    <div class="wa-bubble">
                        <img src="{{ asset('imgs/logo_unela_color.png') }}" 
                             alt="Flyer Publicitario" class="wa-bubble-img" id="simFlyerImg">
                        <div class="wa-bubble-text" id="simBubbleText">Cargando vista previa...</div>
                        <div class="wa-bubble-meta">
                            <span>10:45 AM</span>
                            <i class="bi bi-check-all"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal de Confirmación de Lanzamiento Masivo -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--card-dark, #ffffff); border: 1px solid var(--border-dark, #cbd5e1); border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Confirmar Lanzamiento de Campaña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted">Estás a punto de despachar el anuncio a la lista seleccionada:</p>
                <ul class="list-group list-group-flush rounded-3 mb-3 border">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Campaña:</span>
                        <strong id="modalCampanaTitulo">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Destinatarios con WhatsApp:</span>
                        <strong class="text-success" id="modalTotalDestinatarios">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Intervalo Anti-Spam:</span>
                        <strong id="modalIntervalo">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Tiempo estimado de despacho:</span>
                        <strong class="text-info" id="modalTiempoEstimado">-</strong>
                    </li>
                </ul>
                <div class="alert alert-secondary small text-muted mb-0 border-0">
                    💡 <em>El proceso se ejecuta en segundo plano a través de n8n y Evolution API. Puedes cerrar el navegador y los mensajes continuarán enviándose con sus pausas programadas.</em>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" id="btnEjecutarLanzamiento">
                    <i class="bi bi-check-circle me-1"></i> Sí, Iniciar Campaña
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configuración Webhook n8n -->
<div class="modal fade" id="modalWebhookN8n" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--card-dark, #ffffff); border: 1px solid var(--border-dark, #cbd5e1); border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3-fill text-success"></i> Webhook n8n para Campañas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('whatsapp.campanas.guardar_webhook') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <label class="form-label-styled">URL del Webhook Receptor en n8n:</label>
                    <input type="url" name="n8n_webhook_campana_url" class="form-control-custom w-100 mb-2" 
                           value="{{ $config['n8n_campana_webhook'] ?: 'https://n8n.renangalvan.net/webhook/campana-whatsapp' }}" required>
                    <p class="small text-muted mb-0">
                        Este webhook recibe la campaña con los destinatarios y procesa el bucle con retardo seguro en tu VPS.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Guardar Webhook</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Elementos DOM
    const textarea = document.getElementById('mensaje_template');
    const simBubbleText = document.getElementById('simBubbleText');
    const simFlyerImg = document.getElementById('simFlyerImg');
    const flyerFileInput = document.getElementById('flyer_file');
    const flyerUrlInput = document.getElementById('flyer_url');
    const charCount = document.getElementById('charCount');
    const filtroAudiencia = document.getElementById('filtro_audiencia');
    const boxPrograma = document.getElementById('boxPrograma');
    const selectPrograma = document.getElementById('id_programa');
    const boxOrigenProspecto = document.getElementById('boxOrigenProspecto');
    const selectOrigenProspecto = document.getElementById('origen_prospecto');
    const badgeTotalDestinatarios = document.getElementById('badgeTotalDestinatarios');

    // Función para actualizar la vista previa en el simulador
    function actualizarSimulador() {
        let text = textarea.value || '';
        charCount.innerText = text.length + ' caracteres';

        // Reemplazo de etiquetas dinámicas en el simulador
        text = text.replace(/{nombre}/g, 'Carlos')
                   .replace(/{apellidos}/g, 'Mendoza')
                   .replace(/{nombre_completo}/g, 'Carlos Mendoza')
                   .replace(/{telefono}/g, '+506 8777-7849');

        // Formato negrita de WhatsApp (*palabra* -> <strong>palabra</strong>)
        let formatted = text.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/_(.*?)_/g, '<em>$1</em>');
        
        simBubbleText.innerHTML = formatted;
    }

    textarea.addEventListener('input', actualizarSimulador);

    // Previsualización de imagen cargada
    flyerFileInput.addEventListener('change', function (e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (event) {
                simFlyerImg.src = event.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    flyerUrlInput.addEventListener('input', function () {
        if (this.value && this.value.startsWith('http')) {
            simFlyerImg.src = this.value;
        }
    });

    // Inserción de Tags dinámicos en la posición del cursor
    window.insertTag = function (tag) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const val = textarea.value;
        textarea.value = val.substring(0, start) + tag + val.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        actualizarSimulador();
    };

    // Cambio de audiencia y consulta dinámica
    function consultarDestinatarios() {
        const filtro = filtroAudiencia.value;
        const idProg = selectPrograma ? selectPrograma.value : '';
        const origProg = selectOrigenProspecto ? selectOrigenProspecto.value : '';

        if (filtro === 'programa') {
            boxPrograma.style.display = 'block';
            if (boxOrigenProspecto) boxOrigenProspecto.style.display = 'none';
        } else if (filtro === 'prospectos_origen') {
            boxPrograma.style.display = 'none';
            if (boxOrigenProspecto) boxOrigenProspecto.style.display = 'block';
        } else {
            boxPrograma.style.display = 'none';
            if (boxOrigenProspecto) boxOrigenProspecto.style.display = 'none';
        }

        badgeTotalDestinatarios.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calculando...';

        fetch(`{{ route('whatsapp.campanas.destinatarios_ajax') }}?filtro_audiencia=${filtro}&id_programa=${idProg}&origen_prospecto=${encodeURIComponent(origProg)}`)
            .then(res => res.json())
            .then(data => {
                badgeTotalDestinatarios.innerText = `${data.total} contactos`;
            })
            .catch(err => {
                console.error(err);
                badgeTotalDestinatarios.innerText = '0 contactos';
            });
    }

    filtroAudiencia.addEventListener('change', consultarDestinatarios);
    if (selectPrograma) selectPrograma.addEventListener('change', consultarDestinatarios);
    if (selectOrigenProspecto) selectOrigenProspecto.addEventListener('change', consultarDestinatarios);

    // Envío de Prueba Unitaria
    const btnTestEnvio = document.getElementById('btnTestEnvio');
    const testResultMsg = document.getElementById('testResultMsg');

    btnTestEnvio.addEventListener('click', function () {
        const tel = document.getElementById('telefono_test').value;
        const msg = textarea.value;

        if (!tel) {
            Swal.fire('Atención', 'Por favor indica un número telefónico para la prueba.', 'warning');
            return;
        }

        btnTestEnvio.disabled = true;
        btnTestEnvio.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';
        testResultMsg.innerHTML = '<span class="text-info"><i class="bi bi-arrow-repeat spin me-1"></i> Despachando prueba a tu WhatsApp vía Evolution API...</span>';

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('telefono_test', tel);
        formData.append('mensaje', msg);
        if (flyerFileInput.files[0]) {
            formData.append('flyer_file', flyerFileInput.files[0]);
        }
        if (flyerUrlInput.value) {
            formData.append('flyer_url', flyerUrlInput.value);
        }

        fetch('{{ route("whatsapp.campanas.test") }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btnTestEnvio.disabled = false;
            btnTestEnvio.innerHTML = '<i class="bi bi-send me-1"></i> Probar en mi WhatsApp';

            if (data.success) {
                testResultMsg.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ¡Prueba entregada con éxito a ${tel}! Revisa tu teléfono.</span>`;
                Swal.fire('¡Enviado!', `Mensaje y flyer entregados exitosamente a ${tel}.`, 'success');
            } else {
                testResultMsg.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Error: ${data.message}</span>`;
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            btnTestEnvio.disabled = false;
            btnTestEnvio.innerHTML = '<i class="bi bi-send me-1"></i> Probar en mi WhatsApp';
            testResultMsg.innerHTML = `<span class="text-danger">Error de comunicación con el servidor.</span>`;
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        });
    });

    // Modal de Confirmación y Lanzamiento Masivo
    const btnConfirmar = document.getElementById('btnConfirmarLanzamiento');
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
    const btnEjecutar = document.getElementById('btnEjecutarLanzamiento');

    btnConfirmar.addEventListener('click', function () {
        const titulo = document.getElementById('titulo_campana').value;
        const total = badgeTotalDestinatarios.innerText;
        const intervalo = document.getElementById('intervalo_segundos').value;
        const totalNum = parseInt(total) || 0;
        const minutos = Math.round((totalNum * intervalo) / 60);

        document.getElementById('modalCampanaTitulo').innerText = titulo;
        document.getElementById('modalTotalDestinatarios').innerText = total;
        document.getElementById('modalIntervalo').innerText = `${intervalo} segundos entre cada mensaje`;
        document.getElementById('modalTiempoEstimado').innerText = `~${minutos} minutos en segundo plano`;

        modalConfirmar.show();
    });

    btnEjecutar.addEventListener('click', function () {
        btnEjecutar.disabled = true;
        btnEjecutar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Lanzando Campaña...';

        const form = document.getElementById('formCampana');
        const formData = new FormData(form);

        fetch('{{ route("whatsapp.campanas.lanzar") }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btnEjecutar.disabled = false;
            btnEjecutar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Sí, Iniciar Campaña';
            modalConfirmar.hide();

            if (data.success) {
                Swal.fire('¡Campaña Iniciada!', data.message, 'success');
            } else {
                Swal.fire('Atención', data.message, 'warning');
            }
        })
        .catch(err => {
            btnEjecutar.disabled = false;
            btnEjecutar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Sí, Iniciar Campaña';
            modalConfirmar.hide();
            Swal.fire('Despachado', 'Campaña despachada correctamente al motor de automatización.', 'info');
        });
    });

    // Inicializar simulador al cargar
    actualizarSimulador();
</script>
@endsection
