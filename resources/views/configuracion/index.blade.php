@extends('layouts.app')

@section('title', 'Configuración de la Plataforma - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .config-container {
        padding-top: 1.5rem;
        padding-bottom: 4rem;
    }

    .config-hero {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.9) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .config-nav-pills {
        gap: 0.5rem;
        background-color: var(--card-dark);
        padding: 0.5rem;
        border-radius: 1rem;
        border: 1px solid var(--border-dark);
        margin-bottom: 2rem;
    }

    .config-nav-pills .nav-link {
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .config-nav-pills .nav-link:hover {
        color: #f8fafc;
        background-color: rgba(255, 255, 255, 0.05);
    }

    .config-nav-pills .nav-link.active {
        background-color: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(95, 178, 48, 0.35);
    }

    [data-theme="light"] .config-nav-pills .nav-link.active {
        color: #ffffff !important;
    }

    .config-card {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.75rem;
        overflow: hidden;
    }

    .config-card-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border-dark);
        background-color: rgba(255, 255, 255, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .config-card-body {
        padding: 1.75rem;
    }

    .form-label-custom {
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #f1f5f9;
        margin-bottom: 0.4rem;
    }

    [data-theme="light"] .form-label-custom {
        color: #475569;
    }

    .form-control-custom, .form-select-custom {
        background-color: var(--card-dark) !important;
        border: 1px solid var(--border-dark) !important;
        color: #f8fafc !important;
        border-radius: 0.75rem;
        padding: 0.65rem 1rem;
        transition: all 0.2s ease;
    }

    [data-theme="light"] .form-control-custom,
    [data-theme="light"] .form-select-custom {
        color: #0f172a !important;
        background-color: #ffffff !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.2) !important;
    }

    .logo-preview-box {
        background: rgba(0, 0, 0, 0.25);
        border: 2px dashed var(--border-dark);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
        transition: background 0.3s ease;
    }

    .logo-preview-img {
        max-height: 75px;
        max-width: 240px;
        object-fit: contain;
    }

    .banner-preview-img {
        max-height: 130px;
        max-width: 100%;
        object-fit: contain;
        border-radius: 0.5rem;
    }

    .module-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-dark);
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .module-card:hover {
        border-color: rgba(95, 178, 48, 0.4);
        background: rgba(255, 255, 255, 0.04);
        transform: translateY(-2px);
    }

    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }

    .form-switch .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .test-result-box {
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        display: none;
        margin-top: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 config-container">

    <!-- Hero Header -->
    <div class="config-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 font-monospace small">
                    <i class="bi bi-shield-check me-1"></i> CONTROL ADMINISTRADOR GENERAL
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 font-monospace small">
                    <i class="bi bi-lock-fill me-1"></i> SESIÓN DESBLOQUEADA
                </span>
            </div>
            <h1 class="h2 fw-bold text-white mb-1">
                <i class="bi bi-sliders2-vertical text-primary me-2"></i>Configuración y Programación de la Plataforma
            </h1>
            <p class="text-white-50 mb-0">Gestione parámetros globales, identidad de marca, orquestación de n8n, WhatsApp y modularidad del sistema.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('configuracion.salir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm" title="Bloquear inmediatamente la sesión segura">
                    <i class="bi bi-lock-fill"></i> Bloquear Acceso
                </button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Mensajes de Notificación -->
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4 shadow-sm" style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 mb-4 shadow-sm" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 mb-4 shadow-sm" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Navegación por Pestañas (Pills) -->
    <ul class="nav nav-pills config-nav-pills flex-column flex-md-row" id="configTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'parametros' ? 'active' : '' }}" id="tab-btn-parametros" data-bs-toggle="pill" data-bs-target="#pane-parametros" type="button" role="tab">
                <i class="bi bi-buildings"></i> Parámetros del Sistema
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'identidad' ? 'active' : '' }}" id="tab-btn-identidad" data-bs-toggle="pill" data-bs-target="#pane-identidad" type="button" role="tab">
                <i class="bi bi-palette"></i> Logotipo e Identidad
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'n8n' ? 'active' : '' }}" id="tab-btn-n8n" data-bs-toggle="pill" data-bs-target="#pane-n8n" type="button" role="tab">
                <i class="bi bi-diagram-3-fill"></i> Automatizaciones & n8n
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'modulos' ? 'active' : '' }}" id="tab-btn-modulos" data-bs-toggle="pill" data-bs-target="#pane-modulos" type="button" role="tab">
                <i class="bi bi-toggles2"></i> Módulos y Personalización
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'seguridad' ? 'active' : '' }}" id="tab-btn-seguridad" data-bs-toggle="pill" data-bs-target="#pane-seguridad" type="button" role="tab">
                <i class="bi bi-shield-lock-fill"></i> Clave Maestra & Seguridad
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="configTabsContent">

        <!-- =================================================================== -->
        <!-- PESTAÑA 1: PARÁMETROS DEL SISTEMA                                  -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'parametros' ? 'show active' : '' }}" id="pane-parametros" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="parametros">

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-buildings text-primary fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Datos Institucionales Oficiales</h5>
                                </div>
                                <span class="badge bg-secondary bg-opacity-25 text-white-50">Marca Blanca</span>
                            </div>
                            <div class="config-card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label-custom">Nombre Comercial</label>
                                        <input type="text" name="nombre" class="form-control form-control-custom" value="{{ old('nombre', $cliente['nombre'] ?? 'CEFI') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Siglas</label>
                                        <input type="text" name="siglas" class="form-control form-control-custom" value="{{ old('siglas', $cliente['siglas'] ?? 'CEFI') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Razón Social / Nombre Legal</label>
                                        <input type="text" name="nombre_legal" class="form-control form-control-custom" value="{{ old('nombre_legal', $cliente['nombre_legal'] ?? '') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Eslogan Oficial</label>
                                        <input type="text" name="slogan" class="form-control form-control-custom" value="{{ old('slogan', $cliente['slogan'] ?? '') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Dirección Física</label>
                                        <input type="text" name="direccion" class="form-control form-control-custom" value="{{ old('direccion', $cliente['direccion'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Canales de Contacto Oficiales -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-telephone-inbound text-success fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Canales de Contacto</h5>
                                </div>
                            </div>
                            <div class="config-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Teléfono Oficial (WhatsApp Internacional)</label>
                                        <input type="text" name="telefono" class="form-control form-control-custom" value="{{ old('telefono', $cliente['telefono'] ?? '') }}" placeholder="50687777849">
                                        <div class="form-text text-white-50 small">Formato numérico sin símbolos (ej: 50687777849).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Teléfono Display (Visual en PDF y Web)</label>
                                        <input type="text" name="telefono_display" class="form-control form-control-custom" value="{{ old('telefono_display', $cliente['telefono_display'] ?? '') }}" placeholder="+506 8777-7849">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Email de Soporte Técnico</label>
                                        <input type="email" name="email_soporte" class="form-control form-control-custom" value="{{ old('email_soporte', $cliente['email_soporte'] ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Email de Finanzas / Facturación</label>
                                        <input type="email" name="email_finanzas" class="form-control form-control-custom" value="{{ old('email_finanzas', $cliente['email_finanzas'] ?? '') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Email de Información General</label>
                                        <input type="email" name="email_contacto" class="form-control form-control-custom" value="{{ old('email_contacto', $cliente['email_contacto'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <!-- Enlaces de Plataformas e Integraciones -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-globe2 text-info fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Enlaces de Plataformas</h5>
                                </div>
                            </div>
                            <div class="config-card-body">
                                <div class="mb-3">
                                    <label class="form-label-custom">Sitio Web Oficial</label>
                                    <input type="url" name="sitio_web" class="form-control form-control-custom" value="{{ old('sitio_web', $cliente['sitio_web'] ?? '') }}" placeholder="https://cefi.cr">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Campus Virtual / Aula Virtual</label>
                                    <input type="url" name="campus_virtual" class="form-control form-control-custom" value="{{ old('campus_virtual', $cliente['campus_virtual'] ?? '') }}" placeholder="https://virtual.cefi.cr">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Clave Maestra Moodle Bridge</label>
                                    <input type="text" name="moodle_key" class="form-control form-control-custom font-monospace" value="{{ old('moodle_key', $cliente['moodle_key'] ?? '') }}">
                                    <div class="form-text text-white-50 small">Token de autenticación directa con el conector de notas Moodle.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen y Guardado -->
                        <div class="config-card p-4 text-center">
                            <i class="bi bi-floppy-fill text-primary display-5 mb-3"></i>
                            <h5 class="fw-bold text-white mb-2">Aplicar Cambios Institucionales</h5>
                            <p class="text-white-50 small mb-4">Los parámetros guardados se reflejarán instantáneamente en encabezados, boletas, expedientes y correos.</p>
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Guardar Parámetros Institucionales
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- PESTAÑA 2: LOGOTIPO E IDENTIDAD                                    -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'identidad' ? 'show active' : '' }}" id="pane-identidad" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="active_tab" value="identidad">

                <div class="row g-4">
                    <!-- Logotipo Oficial del Sistema -->
                    <div class="col-lg-6">
                        <div class="config-card h-100">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-image text-primary fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Logotipo del Sistema</h5>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Cabecera & PDFs</span>
                            </div>
                            <div class="config-card-body">
                                <label class="form-label-custom d-block">Logotipo Activo</label>
                                <div class="logo-preview-box mb-3" id="logoPreviewWrapper">
                                    <img src="{{ asset($cliente['logo_url'] ?? 'imgs/logo.png') }}" alt="Logo Oficial" class="logo-preview-img mb-2" id="previewLogo">
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill py-0 px-2" style="font-size: 11px;" onclick="setPreviewBg('logoPreviewWrapper', 'rgba(0,0,0,0.5)')">Fondo Oscuro</button>
                                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill py-0 px-2" style="font-size: 11px;" onclick="setPreviewBg('logoPreviewWrapper', '#ffffff')">Fondo Blanco</button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Subir Nuevo Logotipo (PNG transparente, SVG, WEBP)</label>
                                    <input type="file" name="logo_file" id="logo_file" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this, 'previewLogo')">
                                    <div class="form-text text-white-50 small">Recomendado: Formato vectorial SVG o PNG de 400x120px con fondo transparente.</div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label-custom">O ingresar URL Directa del Logotipo</label>
                                    <input type="text" name="logo_url" class="form-control form-control-custom" value="{{ old('logo_url', $cliente['logo_url'] ?? '') }}" placeholder="/imgs/logo.png o https://...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Banner de Notificaciones / WhatsApp -->
                    <div class="col-lg-6">
                        <div class="config-card h-100">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-card-image text-warning fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Banner Oficial para Avisos & WhatsApp</h5>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Difusión</span>
                            </div>
                            <div class="config-card-body">
                                <label class="form-label-custom d-block">Banner Activo</label>
                                <div class="logo-preview-box mb-3">
                                    <img src="{{ asset($cliente['logo_banner_whatsapp'] ?? 'imgs/fondo_defecto_notificacion.png') }}" alt="Banner Oficial" class="banner-preview-img mb-2" id="previewBanner">
                                    <small class="text-white-50">Se adjunta en recordatorios de pago y campañas de WhatsApp</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Subir Nuevo Banner (JPG, PNG, WEBP)</label>
                                    <input type="file" name="banner_file" id="banner_file" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this, 'previewBanner')">
                                    <div class="form-text text-white-50 small">Recomendado: Formato 1200x630px para perfecta resolución en dispositivos móviles.</div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label-custom">O ingresar URL Directa del Banner</label>
                                    <input type="text" name="banner_url" class="form-control form-control-custom" value="{{ old('banner_url', $cliente['logo_banner_whatsapp'] ?? '') }}" placeholder="/imgs/fondo_defecto_notificacion.png">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Guardado -->
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Guardar Identidad Gráfica & Logos
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- PESTAÑA 3: AUTOMATIZACIONES & n8n / WHATSAPP                       -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'n8n' ? 'show active' : '' }}" id="pane-n8n" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="n8n">

                <div class="row g-4">
                    <!-- n8n Orchestrator -->
                    <div class="col-lg-7">
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-diagram-3-fill text-info fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Orquestación de Flujos n8n</h5>
                                </div>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">Webhook Router</span>
                            </div>
                            <div class="config-card-body">
                                <p class="text-white-50 small mb-4">
                                    Configure los endpoints receptores en su servidor VPS n8n. Los eventos generados por Inbox (morosidad, boletas, difusiones y asistencia) se enviarán directamente a estos webhooks.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label-custom">URL Base del Servidor n8n</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-hdd-network"></i></span>
                                        <input type="url" name="n8n_webhook_base_url" id="n8n_webhook_base_url" class="form-control form-control-custom font-monospace" value="{{ old('n8n_webhook_base_url', $configDb['n8n_webhook_base_url'] ?? ($cliente['n8n']['webhook_base_url'] ?? 'https://n8n.renangalvan.net')) }}" placeholder="https://n8n.tudominio.com">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Webhook: Notificaciones de Morosidad & Cobros</label>
                                    <div class="input-group">
                                        <input type="url" name="n8n_webhook_morosidad_url" id="n8n_morosidad_url" class="form-control form-control-custom font-monospace" value="{{ old('n8n_webhook_morosidad_url', $configDb['n8n_webhook_morosidad_url'] ?? ($cliente['n8n']['webhook_morosidad'] ?? '')) }}" placeholder="https://n8n.tudominio.com/webhook/cefi-morosidad">
                                        <button type="button" class="btn btn-outline-info" onclick="probarN8n('n8n_morosidad_url', 'res-mora')">
                                            <i class="bi bi-lightning-charge"></i> Probar
                                        </button>
                                    </div>
                                    <div id="res-mora" class="test-result-box"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Webhook: Recordatorios de Pago & Vencimientos</label>
                                    <div class="input-group">
                                        <input type="url" name="n8n_webhook_recordatorio_url" id="n8n_recordatorio_url" class="form-control form-control-custom font-monospace" value="{{ old('n8n_webhook_recordatorio_url', $configDb['n8n_webhook_recordatorio_url'] ?? ($cliente['n8n']['webhook_recordatorio'] ?? '')) }}" placeholder="https://n8n.tudominio.com/webhook/cefi-recordatorio">
                                        <button type="button" class="btn btn-outline-info" onclick="probarN8n('n8n_recordatorio_url', 'res-rec')">
                                            <i class="bi bi-lightning-charge"></i> Probar
                                        </button>
                                    </div>
                                    <div id="res-rec" class="test-result-box"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Webhook: Difusión Masiva y Campañas de Oferta Académica</label>
                                    <div class="input-group">
                                        <input type="url" name="n8n_webhook_campana_url" id="n8n_campana_url" class="form-control form-control-custom font-monospace" value="{{ old('n8n_webhook_campana_url', $configDb['n8n_webhook_campana_url'] ?? ($cliente['n8n']['webhook_campana'] ?? '')) }}" placeholder="https://n8n.tudominio.com/webhook/cefi-campana">
                                        <button type="button" class="btn btn-outline-info" onclick="probarN8n('n8n_campana_url', 'res-camp')">
                                            <i class="bi bi-lightning-charge"></i> Probar
                                        </button>
                                    </div>
                                    <div id="res-camp" class="test-result-box"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp API (Evolution API / Green-API) -->
                    <div class="col-lg-5">
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-whatsapp text-success fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Pasarela WhatsApp & VPS</h5>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Evolution API</span>
                            </div>
                            <div class="config-card-body">
                                <div class="mb-3">
                                    <label class="form-label-custom">URL del Servicio Evolution API</label>
                                    <input type="url" name="evolution_api_url" class="form-control form-control-custom font-monospace" value="{{ old('evolution_api_url', $waConfig['evolution_url'] ?? 'http://93.127.215.91:8080') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Nombre de Instancia Activa</label>
                                    <input type="text" name="evolution_instance" class="form-control form-control-custom font-monospace" value="{{ old('evolution_instance', $waConfig['evolution_instance'] ?? 'cefi_whatsapp') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">API Key / Token Secreto</label>
                                    <input type="password" name="evolution_api_key" class="form-control form-control-custom font-monospace" value="{{ old('evolution_api_key', $waConfig['evolution_key'] ?? '') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Teléfono Administrador para Alertas</label>
                                    <input type="text" name="whatsapp_phone" class="form-control form-control-custom" value="{{ old('whatsapp_phone', $configDb['whatsapp_phone'] ?? $cliente['telefono']) }}" placeholder="50687777849">
                                </div>

                                <div class="border-top border-secondary border-opacity-25 pt-3 mt-4">
                                    <h6 class="fw-bold text-white small text-uppercase mb-2"><i class="bi bi-send-check text-success me-1"></i> Prueba de Despacho WhatsApp</h6>
                                    <div class="input-group mb-2">
                                        <input type="text" id="test_wa_phone" class="form-control form-control-custom" placeholder="Número con código de país (ej: 50687777849)">
                                        <button type="button" class="btn btn-outline-success" onclick="probarWhatsApp()">
                                            <i class="bi bi-whatsapp"></i> Test
                                        </button>
                                    </div>
                                    <div id="res-wa" class="test-result-box"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Guardado n8n -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Guardar Parámetros de n8n & WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- PESTAÑA 4: MÓDULOS Y PERSONALIZACIÓN                               -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'modulos' ? 'show active' : '' }}" id="pane-modulos" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="modulos">
                <input type="hidden" name="submitted_modules" value="1">

                <div class="config-card">
                    <div class="config-card-header">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-toggles2 text-primary fs-5"></i>
                                <h5 class="fw-bold mb-0 text-white">Control de Activación Modular (Feature Flags)</h5>
                            </div>
                            <small class="text-white-50">Active o desactive módulos de la plataforma según la contratación o requerimientos específicos de la institución.</small>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-floppy2-fill"></i> Guardar Módulos
                        </button>
                    </div>
                    <div class="config-card-body">
                        <div class="row g-3">
                            @foreach ($modulos as $key => $mod)
                                <div class="col-md-6 col-xl-4">
                                    <div class="module-card">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-secondary bg-opacity-25 text-white-50 small">{{ $mod['categoria'] ?? 'General' }}</span>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="modules[{{ $key }}]" value="1" id="mod_{{ $key }}" {{ $mod['enabled'] ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            <h6 class="fw-bold text-white mb-1">{{ $mod['nombre'] }}</h6>
                                            <p class="text-white-50 small mb-2" style="font-size: 0.8rem; line-height: 1.35;">{{ $mod['descripcion'] }}</p>
                                        </div>
                                        <div class="pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                            <code class="text-info x-small font-monospace">{{ $key }}</code>
                                            @if($mod['enabled'])
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 x-small"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-white-50 x-small"><i class="bi bi-dash-circle me-1"></i>Desactivado</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Guardar Estado de Módulos
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- PESTAÑA 5: SEGURIDAD & CLAVE MAESTRA                               -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'seguridad' ? 'show active' : '' }}" id="pane-seguridad" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="seguridad">

                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-lock-fill text-danger fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Clave de Seguridad Superior (Control de Acceso)</h5>
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Protección Crítica</span>
                            </div>
                            <div class="config-card-body">
                                <div class="alert alert-info border-0 rounded-4 d-flex align-items-start mb-4" style="background-color: rgba(59, 130, 246, 0.12); color: #93c5fd;">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
                                    <div class="small">
                                        Esta clave es el segundo factor de seguridad que blinda la parametrización técnica de la plataforma. Ningún operador regular, administrativo o docente puede ingresar a este módulo sin esta clave.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label-custom">Nueva Clave de Seguridad Superior</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="nueva_clave_maestra" id="nueva_clave_maestra" class="form-control form-control-custom border-start-0" placeholder="••••••••••••" minlength="4">
                                    </div>
                                    <div class="form-text text-white-50 small mt-1">
                                        Deje en blanco si no desea alterar la clave actual. Clave inicial estándar: <code class="text-info">cefi2026</code>.
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-danger px-5 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check"></i> Actualizar Clave Maestra de Seguridad
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function previewImage(input, targetId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(targetId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function setPreviewBg(wrapperId, color) {
        document.getElementById(wrapperId).style.backgroundColor = color;
    }

    function probarN8n(inputId, resultBoxId) {
        const url = document.getElementById(inputId).value;
        const resBox = document.getElementById(resultBoxId);
        
        if (!url) {
            alert('Por favor ingrese la URL del webhook en el campo.');
            return;
        }

        resBox.style.display = 'block';
        resBox.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
        resBox.style.color = '#93c5fd';
        resBox.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> Probando conectividad con servidor n8n...';

        fetch('{{ route("configuracion.test_n8n") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url: url })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resBox.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
                resBox.style.color = '#34d399';
                resBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
            } else {
                resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
                resBox.style.color = '#f87171';
                resBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + data.message;
            }
        })
        .catch(err => {
            resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            resBox.style.color = '#f87171';
            resBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error de red: ' + err;
        });
    }

    function probarWhatsApp() {
        const phone = document.getElementById('test_wa_phone').value;
        const resBox = document.getElementById('res-wa');

        if (!phone) {
            alert('Por favor ingrese el número telefónico para la prueba.');
            return;
        }

        resBox.style.display = 'block';
        resBox.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
        resBox.style.color = '#93c5fd';
        resBox.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> Despachando mensaje de prueba vía Evolution API...';

        fetch('{{ route("configuracion.test_whatsapp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                destinatario: phone,
                mensaje: '👋 *Mensaje de Prueba CEFI Inbox*\n\nConexión exitosa verificada desde el Panel de Configuración de la plataforma.'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resBox.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
                resBox.style.color = '#34d399';
                resBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
            } else {
                resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
                resBox.style.color = '#f87171';
                resBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + data.message;
            }
        })
        .catch(err => {
            resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            resBox.style.color = '#f87171';
            resBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error al contactar servicio WhatsApp: ' + err;
        });
    }
</script>
@endsection
