@extends('layouts.app')

@section('title', 'Parámetros del Sistema e Identidad - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .config-container {
        padding-top: 1.5rem;
        padding-bottom: 3.5rem;
    }

    .config-card {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .config-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-dark);
        background-color: rgba(255, 255, 255, 0.02);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .config-card-body {
        padding: 1.5rem;
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
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.2) !important;
    }

    .logo-preview-box {
        background: rgba(0, 0, 0, 0.2);
        border: 2px dashed var(--border-dark);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
    }

    [data-theme="light"] .logo-preview-box {
        background: #f8fafc;
    }

    .logo-preview-img {
        max-height: 70px;
        max-width: 220px;
        object-fit: contain;
    }

    .banner-preview-img {
        max-height: 120px;
        max-width: 100%;
        object-fit: contain;
        border-radius: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 config-container">
    
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold text-white mb-1">
                <i class="bi bi-gear-wide-connected text-primary me-2"></i>Parámetros del Sistema & Identidad
            </h1>
            <p class="text-white-50 mb-0">Configure el logotipo oficial, la información institucional de la entidad, plataformas y canales oficiales.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('configuracion.parametros.salir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2 d-flex align-items-center gap-1" title="Bloquear acceso seguro a parámetros">
                    <i class="bi bi-shield-lock-fill"></i> Bloquear Acceso
                </button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4 shadow-sm" style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
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

    <form action="{{ route('configuracion.parametros.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Columna Izquierda: Identidad y Logotipo -->
            <div class="col-lg-5">
                
                <!-- Logotipo del Sistema -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-image text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Logotipo del Sistema</h5>
                    </div>
                    <div class="config-card-body">
                        <div class="mb-3">
                            <label class="form-label-custom d-block">Logotipo Actual</label>
                            <div class="logo-preview-box mb-3">
                                <img src="{{ asset($cliente['logo_url'] ?? 'imgs/logo.png') }}" alt="Logo Oficial" class="logo-preview-img mb-2" id="previewLogo">
                                <small class="text-white-50">Vista previa del encabezado y documentos oficiales</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Subir Nuevo Logo (PNG, SVG, JPG, WEBP)</label>
                            <input type="file" name="logo_file" id="logo_file" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this, 'previewLogo')">
                            <div class="form-text text-white-50 small">Recomendado: Formato PNG transparente o SVG de alta resolución.</div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label-custom">O ingresar URL Directa del Logo</label>
                            <input type="text" name="logo_url" class="form-control form-control-custom" value="{{ old('logo_url', $cliente['logo_url'] ?? '') }}" placeholder="/imgs/logo.png o https://...">
                        </div>
                    </div>
                </div>

                <!-- Banner Institucional (Boletas & Notificaciones) -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-card-image text-warning fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Banner de Notificaciones</h5>
                    </div>
                    <div class="config-card-body">
                        <div class="mb-3">
                            <label class="form-label-custom d-block">Banner Actual</label>
                            <div class="logo-preview-box mb-3">
                                <img src="{{ asset($cliente['logo_banner_whatsapp'] ?? 'imgs/fondo_defecto_notificacion.png') }}" alt="Banner Oficial" class="banner-preview-img mb-2" id="previewBanner">
                                <small class="text-white-50">Se adjunta en avisos oficiales de pago y WhatsApp</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Subir Nuevo Banner</label>
                            <input type="file" name="banner_file" id="banner_file" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this, 'previewBanner')">
                        </div>

                        <div class="mb-2">
                            <label class="form-label-custom">O ingresar URL del Banner</label>
                            <input type="text" name="banner_url" class="form-control form-control-custom" value="{{ old('banner_url', $cliente['logo_banner_whatsapp'] ?? '') }}" placeholder="/imgs/fondo_defecto_notificacion.png">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Parámetros Institucionales y Plataformas -->
            <div class="col-lg-7">
                
                <!-- Datos Institucionales -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-buildings text-success fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Datos de la Institución (Marca Blanca)</h5>
                    </div>
                    <div class="config-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Nombre Comercial</label>
                                <input type="text" name="nombre" class="form-control form-control-custom" value="{{ old('nombre', $cliente['nombre'] ?? 'CEFI') }}" required>
                                <small class="text-white-50">Ej: CEFI</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Siglas Institucionales</label>
                                <input type="text" name="siglas" class="form-control form-control-custom" value="{{ old('siglas', $cliente['siglas'] ?? 'CEFI') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Razón Social / Nombre Legal</label>
                                <input type="text" name="nombre_legal" class="form-control form-control-custom" value="{{ old('nombre_legal', $cliente['nombre_legal'] ?? 'Centro de Estudios Financieros e Internacionales') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Eslogan Oficial</label>
                                <input type="text" name="slogan" class="form-control form-control-custom" value="{{ old('slogan', $cliente['slogan'] ?? 'Excelencia y Liderazgo Académico') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Dirección Física</label>
                                <input type="text" name="direccion" class="form-control form-control-custom" value="{{ old('direccion', $cliente['direccion'] ?? 'San José, Costa Rica') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlaces de Plataformas & Moodle Bridge -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-link-45deg text-info fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Enlaces de Plataformas & Moodle</h5>
                    </div>
                    <div class="config-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Sitio Web Oficial</label>
                                <input type="url" name="sitio_web" class="form-control form-control-custom" value="{{ old('sitio_web', $cliente['sitio_web'] ?? 'https://cefi.cr') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Campus Virtual Moodle</label>
                                <input type="url" name="campus_virtual" class="form-control form-control-custom" value="{{ old('campus_virtual', $cliente['campus_virtual'] ?? 'https://virtual.cefi.cr') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Clave Maestra Moodle Bridge</label>
                                <input type="text" name="moodle_key" class="form-control form-control-custom font-monospace" value="{{ old('moodle_key', $cliente['moodle_key'] ?? 'cefi2026') }}">
                                <small class="text-white-50">Clave para sincronizar notas y estudiantes con Moodle</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canales de Contacto y Alertas -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-envelope-at text-warning fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Canales de Contacto & Alertas</h5>
                    </div>
                    <div class="config-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Teléfono Oficial (WhatsApp)</label>
                                <input type="text" name="telefono" class="form-control form-control-custom" value="{{ old('telefono', $cliente['telefono'] ?? '50687777849') }}" placeholder="50687777849">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Teléfono Formato Display</label>
                                <input type="text" name="telefono_display" class="form-control form-control-custom" value="{{ old('telefono_display', $cliente['telefono_display'] ?? '+506 8777-7849') }}" placeholder="+506 8777-7849">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Email de Soporte</label>
                                <input type="email" name="email_soporte" class="form-control form-control-custom" value="{{ old('email_soporte', $cliente['email_soporte'] ?? 'soporte@cefi.cr') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Email de Finanzas / Cobros</label>
                                <input type="email" name="email_finanzas" class="form-control form-control-custom" value="{{ old('email_finanzas', $cliente['email_finanzas'] ?? 'finanzas@cefi.cr') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">WhatsApp Admin para Alertas</label>
                                <input type="text" name="whatsapp_phone" class="form-control form-control-custom" value="{{ old('whatsapp_phone', $configDb['whatsapp_phone'] ?? '50687777849') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Token API de Alertas (Opcional)</label>
                                <input type="text" name="whatsapp_api_key" class="form-control form-control-custom" value="{{ old('whatsapp_api_key', $configDb['whatsapp_api_key'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seguridad & Clave de Acceso Superior -->
                <div class="config-card">
                    <div class="config-card-header">
                        <i class="bi bi-shield-lock-fill text-danger fs-5"></i>
                        <h5 class="fw-bold mb-0 text-white">Seguridad & Clave de Acceso Superior</h5>
                    </div>
                    <div class="config-card-body">
                        <p class="text-white-50 small mb-3">
                            Esta clave protege este módulo con un control superior para que únicamente el personal directivo o autorizado pueda modificar el logotipo y los parámetros institucionales.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label-custom">Nueva Clave de Acceso Superior</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="nueva_clave_maestra" class="form-control form-control-custom border-start-0" placeholder="Dejar en blanco para conservar la clave actual" autocomplete="new-password">
                                </div>
                                <div class="form-text text-white-50 mt-1 small">
                                    <i class="bi bi-info-circle me-1"></i>Deje en blanco si no desea cambiar la clave. Clave inicial estándar: <code class="text-info">cefi2026</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardado Principal -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg d-flex align-items-center gap-2 fs-6">
                        <i class="bi bi-check-circle-fill"></i> Guardar Todos los Parámetros
                    </button>
                </div>

            </div>
        </div>
    </form>
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
</script>
@endsection
