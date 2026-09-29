@extends('layouts.app')

@section('title', 'Configuración de la Plataforma - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .config-container {
        padding-top: 1.5rem;
        padding-bottom: 4rem;
    }

    .config-hero {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.95) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    /* Overrides para tema claro en Hero */
    [data-theme="light"] .config-hero {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    }

    [data-theme="light"] .config-hero h1 {
        color: #0f172a !important;
    }

    [data-theme="light"] .config-hero p {
        color: #475569 !important;
    }

    [data-theme="light"] .config-hero .btn-outline-danger {
        color: #dc2626 !important;
        background-color: #fef2f2 !important;
        border-color: #fca5a5 !important;
    }

    [data-theme="light"] .config-hero .btn-outline-danger:hover {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
    }

    [data-theme="light"] .config-hero .btn-outline-secondary {
        color: #334155 !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }

    [data-theme="light"] .config-hero .btn-outline-secondary:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    [data-theme="light"] .badge-admin {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fde68a !important;
    }

    [data-theme="light"] .badge-unlocked {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }

    .config-nav-pills {
        gap: 0.5rem;
        background-color: var(--card-dark);
        padding: 0.5rem;
        border-radius: 1rem;
        border: 1px solid var(--border-dark);
        margin-bottom: 2rem;
    }

    [data-theme="light"] .config-nav-pills {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03) !important;
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

    [data-theme="light"] .config-nav-pills .nav-link {
        color: #64748b !important;
    }

    .config-nav-pills .nav-link:hover {
        color: #f8fafc;
        background-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="light"] .config-nav-pills .nav-link:hover {
        color: #0f172a !important;
        background-color: #f1f5f9 !important;
    }

    .config-nav-pills .nav-link.active {
        background-color: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(95, 178, 48, 0.35);
    }

    [data-theme="light"] .config-nav-pills .nav-link.active {
        background-color: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(95, 178, 48, 0.25) !important;
    }

    .config-card {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.75rem;
        overflow: hidden;
    }

    [data-theme="light"] .config-card {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03) !important;
    }

    .config-card-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border-dark);
        background-color: rgba(255, 255, 255, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    [data-theme="light"] .config-card-header {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    [data-theme="light"] .config-card-header h5 {
        color: #0f172a !important;
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
        color: #334155 !important;
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
        border: 1px solid #cbd5e1 !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.2) !important;
    }

    [data-theme="light"] .input-group-text {
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
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

    [data-theme="light"] .logo-preview-box {
        background: #f8fafc !important;
        border: 2px dashed #cbd5e1 !important;
    }

    [data-theme="light"] .logo-preview-box small {
        color: #64748b !important;
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

    [data-theme="light"] .module-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }

    .module-card:hover {
        border-color: rgba(95, 178, 48, 0.4);
        background: rgba(255, 255, 255, 0.04);
        transform: translateY(-2px);
    }

    [data-theme="light"] .module-card:hover {
        background: #ffffff !important;
        border-color: var(--primary) !important;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06) !important;
    }

    [data-theme="light"] .module-card h6 {
        color: #0f172a !important;
    }

    [data-theme="light"] .module-card p {
        color: #64748b !important;
    }

    [data-theme="light"] .module-card code {
        color: #0284c7 !important;
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

    /* Alertas */
    .alert-config-success {
        background-color: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
    }
    [data-theme="light"] .alert-config-success {
        background-color: #ecfdf5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }
    [data-theme="light"] .alert-config-success .bi {
        color: #059669 !important;
    }

    .alert-config-danger {
        background-color: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
    }
    [data-theme="light"] .alert-config-danger {
        background-color: #fef2f2 !important;
        color: #991b1b !important;
        border: 1px solid #fecaca !important;
    }

    .alert-config-info {
        background-color: rgba(59, 130, 246, 0.15);
        color: #93c5fd;
        border: 1px solid rgba(59, 130, 246, 0.3) !important;
    }
    [data-theme="light"] .alert-config-info {
        background-color: #eff6ff !important;
        color: #1e40af !important;
        border: 1px solid #bfdbfe !important;
    }

    .test-result-box {
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        display: none;
        margin-top: 0.75rem;
    }

    [data-theme="light"] .modal-content {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
    }
    [data-theme="light"] .modal-header {
        border-bottom-color: #e2e8f0 !important;
    }
    [data-theme="light"] .modal-footer {
        border-top-color: #e2e8f0 !important;
    }
    [data-theme="light"] .modal-content .btn-close-white {
        filter: invert(1) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 config-container">

    <!-- Hero Header -->
    <div class="config-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-admin bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 font-monospace small">
                    <i class="bi bi-shield-check me-1"></i> CONTROL ADMINISTRADOR GENERAL
                </span>
                <span class="badge badge-unlocked bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 font-monospace small">
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
        <div class="alert alert-config-success border-0 rounded-4 d-flex align-items-center mb-4 shadow-sm">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-config-danger border-0 rounded-4 mb-4 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-config-danger border-0 rounded-4 mb-4 shadow-sm">
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
            <button class="nav-link {{ $activeTab === 'smtp' ? 'active' : '' }}" id="tab-btn-smtp" data-bs-toggle="pill" data-bs-target="#pane-smtp" type="button" role="tab">
                <i class="bi bi-envelope-at-fill"></i> Correo Saliente (SMTP)
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
                                    <label class="form-label-custom">Webhook: Notificaciones de Boletas & Firma Digital</label>
                                    <div class="input-group">
                                        <input type="url" name="n8n_webhook_boleta_url" id="n8n_boleta_url" class="form-control form-control-custom font-monospace" value="{{ old('n8n_webhook_boleta_url', $configDb['n8n_webhook_boleta_url'] ?? ($cliente['n8n']['webhook_boleta'] ?? '')) }}" placeholder="https://n8n.tudominio.com/webhook/cefi-boleta">
                                        <button type="button" class="btn btn-outline-info" onclick="probarN8n('n8n_boleta_url', 'res-boleta')">
                                            <i class="bi bi-lightning-charge"></i> Probar
                                        </button>
                                    </div>
                                    <div id="res-boleta" class="test-result-box"></div>
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
        <!-- PESTAÑA: CORREO SALIENTE (SMTP)                                     -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'smtp' ? 'show active' : '' }}" id="pane-smtp" role="tabpanel">
            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="smtp">

                <div class="row g-4">
                    <!-- Formulario de Configuración SMTP -->
                    <div class="col-lg-7">
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-envelope-at-fill text-warning fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Servidor de Correo Saliente (SMTP)</h5>
                                </div>
                                <span class="badge bg-warning bg-opacity-25 text-warning">Boletas & Notificaciones</span>
                            </div>
                            <div class="config-card-body">
                                <p class="text-white-50 small mb-4">
                                    Configure la cuenta y el servidor SMTP que el sistema utilizará para despachar las boletas oficiales de matrícula, enlaces de firma digital y recordatorios a los estudiantes.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label-custom">Servidor SMTP (Host)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                            <input type="text" name="mail_host" id="mail_host" class="form-control form-control-custom" value="{{ old('mail_host', $smtpConfig['host'] ?? 'smtp.gmail.com') }}" placeholder="smtp.gmail.com o mail.tudominio.com" required>
                                        </div>
                                        <small class="text-white-50">Para Gmail use <code>smtp.gmail.com</code>. Para hosting propio use <code>mail.tudominio.com</code>.</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-custom">Puerto</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-plug"></i></span>
                                            <input type="number" name="mail_port" id="mail_port" class="form-control form-control-custom" value="{{ old('mail_port', $smtpConfig['port'] ?? 465) }}" placeholder="465" required>
                                        </div>
                                        <small class="text-white-50">465 (SSL) o 587 (TLS)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-custom">Seguridad / Encriptación</label>
                                        <select name="mail_encryption" id="mail_encryption" class="form-select form-select-custom">
                                            <option value="ssl" {{ ($smtpConfig['encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL (Recomendado para puerto 465)</option>
                                            <option value="tls" {{ ($smtpConfig['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS (Recomendado para puerto 587)</option>
                                            <option value="null" {{ ($smtpConfig['encryption'] ?? '') === 'null' ? 'selected' : '' }}>Sin Encriptación (Puerto 25 / Local)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-custom">Nombre Mostrado del Remitente</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" name="mail_from_name" id="mail_from_name" class="form-control form-control-custom" value="{{ old('mail_from_name', $smtpConfig['from_name'] ?? 'Inbox CEFI') }}" placeholder="Ej: Inbox CEFI" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-custom">Usuario / Correo Saliente</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" name="mail_username" id="mail_username" class="form-control form-control-custom" value="{{ old('mail_username', $smtpConfig['username'] ?? '') }}" placeholder="usuario@dominio.com" required>
                                        </div>
                                        <small class="text-white-50">La cuenta que autentica en el servidor de correo.</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-custom">Contraseña / Clave de Aplicación</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" name="mail_password" id="mail_password" class="form-control form-control-custom" value="{{ old('mail_password', $smtpConfig['password'] ?? '') }}" placeholder="Contraseña o app password">
                                            <button class="btn btn-outline-secondary" type="button" onclick="toggleSmtpPass()" title="Mostrar u ocultar contraseña">
                                                <i class="bi bi-eye" id="mail_password_icon"></i>
                                            </button>
                                        </div>
                                        <small class="text-white-50">En Gmail use una Contraseña de Aplicación de 16 caracteres.</small>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-custom">Dirección "De" (From Address)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-reply"></i></span>
                                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control form-control-custom" value="{{ old('mail_from_address', $smtpConfig['from_address'] ?? $smtpConfig['username'] ?? '') }}" placeholder="notificaciones@dominio.com">
                                        </div>
                                        <small class="text-white-50">Dirección que verán los estudiantes como remitente del mensaje.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="config-card-footer text-end p-3">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-save me-1"></i> Guardar Configuración de Correo
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Lateral: Prueba de Envío y Guía Rápida -->
                    <div class="col-lg-5">
                        <!-- Tarjeta Prueba de Envío -->
                        <div class="config-card mb-4">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-send-check text-success fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Comprobación en Vivo</h5>
                                </div>
                                <span class="badge bg-success bg-opacity-25 text-success">Test Directo</span>
                            </div>
                            <div class="config-card-body">
                                <p class="text-white-50 small mb-3">
                                    Pruebe de inmediato la conexión con el servidor. Se enviará un correo electrónico de diagnóstico con los parámetros ingresados.
                                </p>
                                <div class="mb-3">
                                    <label class="form-label-custom">Enviar Correo de Prueba a:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-at"></i></span>
                                        <input type="email" id="test_smtp_email" class="form-control form-control-custom" value="{{ auth()->user()->email ?? 'renangalvan@gmail.com' }}" placeholder="ejemplo@correo.com">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-info w-100 py-2 fw-semibold rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="probarSmtp()">
                                    <i class="bi bi-send-fill"></i> Despachar Correo de Prueba
                                </button>
                                <div id="res-smtp" class="test-result-box mt-3" style="display: none; padding: 12px; border-radius: 8px; font-size: 13px;"></div>
                            </div>
                        </div>

                        <!-- Tarjeta de Instrucciones y Proveedores -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-info fs-5"></i>
                                    <h5 class="fw-bold mb-0 text-white">Guía de Configuración</h5>
                                </div>
                            </div>
                            <div class="config-card-body small text-white-50">
                                <div class="mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                                    <strong class="text-white d-block mb-1"><i class="bi bi-google text-danger me-1"></i> Google / Gmail / Workspace:</strong>
                                    <span>Servidor: <code>smtp.gmail.com</code> | Puerto: <code>465</code> (SSL)<br>
                                    Requiere activar "Verificación en 2 pasos" en la cuenta de Google y generar una <strong>Contraseña de Aplicación</strong> de 16 caracteres.</span>
                                </div>
                                <div class="mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                                    <strong class="text-white d-block mb-1"><i class="bi bi-globe text-primary me-1"></i> Correo de Dominio Propio (cPanel / Webmail):</strong>
                                    <span>Servidor: <code>mail.sudominio.com</code> | Puerto: <code>465</code> (SSL) o <code>587</code> (TLS)<br>
                                    Utilice el correo y contraseña completos creados en su hosting cPanel.</span>
                                </div>
                                <div>
                                    <strong class="text-white d-block mb-1"><i class="bi bi-microsoft text-info me-1"></i> Microsoft 365 / Outlook:</strong>
                                    <span>Servidor: <code>smtp.office365.com</code> | Puerto: <code>587</code> (TLS).</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- PESTAÑA 4: MÓDULOS Y PERSONALIZACIÓN (PROTEGIDA POR LICENCIA)       -->
        <!-- =================================================================== -->
        <div class="tab-pane fade {{ $activeTab === 'modulos' ? 'show active' : '' }}" id="pane-modulos" role="tabpanel">

            @if($devUnlocked)
                <!-- Banner Modo Fabricante / Desarrollador Desbloqueado -->
                <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 shadow-sm" style="background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-shield-lock-fill fs-3 text-warning"></i>
                        <div>
                            <strong class="d-block text-warning fw-bold">Modo Fabricante / Desarrollador Activado</strong>
                            <small class="text-white-50">Tiene permisos exclusivos para activar, desactivar y licenciar módulos en esta instalación.</small>
                        </div>
                    </div>
                    <form action="{{ route('configuracion.dev_bloquear') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1">
                            <i class="bi bi-lock-fill"></i> Bloquear Modo Fabricante
                        </button>
                    </form>
                </div>
            @else
                <!-- Banner Modo Cliente (Solo Lectura) -->
                <div class="alert alert-config-info border-0 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-shield-check fs-3 text-info"></i>
                        <div>
                            <strong class="d-block text-white fw-bold">Plan de Módulos Contratado (Solo Lectura)</strong>
                            <small class="text-white-50">Los módulos activos corresponden a las licencias adquiridas para esta institución. Los interruptores están protegidos.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cliente['telefono'] ?? '50687777849') }}?text={{ urlencode('Hola, deseo consultar sobre la activación de módulos adicionales para nuestra plataforma Inbox CEFI.') }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1">
                            <i class="bi bi-whatsapp"></i> Contratar Módulos
                        </a>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalDevKey">
                            <i class="bi bi-key-fill text-warning"></i> Modo Desarrollador
                        </button>
                    </div>
                </div>
            @endif

            <form action="{{ route('configuracion.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="modulos">
                @if($devUnlocked)
                    <input type="hidden" name="submitted_modules" value="1">
                @endif

                <div class="config-card">
                    <div class="config-card-header">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-toggles2 text-primary fs-5"></i>
                                <h5 class="fw-bold mb-0 text-white">Catálogo de Módulos del Sistema</h5>
                            </div>
                            <small class="text-white-50">
                                @if($devUnlocked)
                                    Modifique el estado de las características contratadas y presione Guardar.
                                @else
                                    Muestra el estado de cada módulo contratado en su suscripción actual.
                                @endif
                            </small>
                        </div>
                        @if($devUnlocked)
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold d-flex align-items-center gap-2 shadow">
                                <i class="bi bi-floppy2-fill"></i> Guardar Licencia de Módulos
                            </button>
                        @endif
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
                                                    @if($devUnlocked)
                                                        <input class="form-check-input" type="checkbox" name="modules[{{ $key }}]" value="1" id="mod_{{ $key }}" {{ $mod['enabled'] ? 'checked' : '' }}>
                                                    @else
                                                        <input class="form-check-input" type="checkbox" disabled {{ $mod['enabled'] ? 'checked' : '' }} title="Protegido por licencia comercial">
                                                    @endif
                                                </div>
                                            </div>
                                            <h6 class="fw-bold text-white mb-1">{{ $mod['nombre'] }}</h6>
                                            <p class="text-white-50 small mb-2" style="font-size: 0.8rem; line-height: 1.35;">{{ $mod['descripcion'] }}</p>
                                        </div>
                                        <div class="pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                            <code class="text-info x-small font-monospace">{{ $key }}</code>
                                            @if($mod['enabled'])
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 x-small"><i class="bi bi-check-circle-fill me-1"></i>Contratado / Activo</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-white-50 x-small"><i class="bi bi-lock-fill me-1"></i>No contratado</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($devUnlocked)
                            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i> Guardar Licencia de Módulos
                                </button>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2 text-white-50 small">
                                <div><i class="bi bi-info-circle me-1"></i> Para habilitar módulos adicionales, contacte al proveedor oficial del software.</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalDevKey">
                                    <i class="bi bi-shield-lock me-1"></i> Desbloquear como Desarrollador
                                </button>
                            </div>
                        @endif
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
                                <div class="alert alert-config-info border-0 rounded-4 d-flex align-items-start mb-4">
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

<!-- Modal Desbloqueo de Fabricante / Desarrollador -->
<div class="modal fade" id="modalDevKey" tabindex="-1" aria-labelledby="modalDevKeyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-dark); border: 1px solid var(--border-dark); border-radius: 1.25rem; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fbbf24;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalDevKeyLabel">Licenciamiento de Fabricante</h5>
                        <small class="text-white-50">Acceso exclusivo para el desarrollador del sistema</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('configuracion.dev_desbloquear') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    <p class="text-white-50 small mb-4">
                        Para habilitar la edición y activación de módulos comerciales en esta instalación, ingrese la <strong>Clave Secreta de Fabricante</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label-custom">Clave Secreta de Fabricante</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="clave_desarrollador" id="clave_desarrollador" class="form-control form-control-custom border-start-0 border-end-0" placeholder="••••••••••••" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility('clave_desarrollador', 'iconDevPass')" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);">
                                <i class="bi bi-eye" id="iconDevPass"></i>
                            </button>
                        </div>
                        <div class="form-text text-white-50 small mt-1">
                            <i class="bi bi-info-circle me-1"></i>Esta clave está definida en el entorno seguro del servidor por el desarrollador.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-unlock-fill"></i> Validar y Desbloquear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

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

    function toggleSmtpPass() {
        const input = document.getElementById('mail_password');
        const icon = document.getElementById('mail_password_icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function probarSmtp() {
        const email = document.getElementById('test_smtp_email').value;
        const resBox = document.getElementById('res-smtp');

        if (!email) {
            Swal.fire('Atención', 'Por favor ingrese el correo electrónico destinatario para la prueba.', 'warning');
            return;
        }

        resBox.style.display = 'block';
        resBox.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
        resBox.style.color = '#93c5fd';
        resBox.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> Conectando con el servidor SMTP y despachando correo de prueba...';

        const payload = {
            email: email,
            host: document.getElementById('mail_host').value,
            port: document.getElementById('mail_port').value,
            encryption: document.getElementById('mail_encryption').value,
            username: document.getElementById('mail_username').value,
            password: document.getElementById('mail_password').value,
            from_address: document.getElementById('mail_from_address').value,
            from_name: document.getElementById('mail_from_name').value
        };

        fetch('{{ route("configuracion.test_smtp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resBox.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
                resBox.style.color = '#34d399';
                resBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
                Swal.fire({
                    icon: 'success',
                    title: '¡Prueba SMTP Exitosa!',
                    text: data.message,
                    confirmButtonColor: '#1066ad'
                });
            } else {
                resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
                resBox.style.color = '#f87171';
                resBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + data.message;
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo de Conexión SMTP',
                    text: data.message
                });
            }
        })
        .catch(err => {
            resBox.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            resBox.style.color = '#f87171';
            resBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error al conectar: ' + err;
            Swal.fire('Error de Red', err.message, 'error');
        });
    }
</script>
@endsection
