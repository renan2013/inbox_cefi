@extends('layouts.app')

@section('title', 'Inbox BPM - Automatización de WhatsApp con n8n')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .settings-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.65rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: #25d366;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .btn-wa-save {
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.25);
        }

        .btn-wa-save:hover {
            background: linear-gradient(135deg, #20ba5a 0%, #0e7065 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.35);
            color: white;
        }

        .badge-status-n8n {
            background: rgba(255, 109, 90, 0.15);
            color: #ff6d5a;
            border: 1px solid rgba(255, 109, 90, 0.35);
            border-radius: 2rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .badge-status-online {
            background: rgba(37, 211, 102, 0.15);
            color: #25d366;
            border: 1px solid rgba(37, 211, 102, 0.3);
            border-radius: 2rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .logo-preview-box {
            background-color: #ffffff;
            border: 2px dashed var(--border-dark);
            border-radius: 1rem;
            padding: 1rem;
            text-align: center;
            max-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-preview-box img {
            max-height: 100px;
            max-width: 100%;
            object-fit: contain;
        }

        .webhook-badge {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 0.5rem;
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">

        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-status-online"><i class="bi bi-check-circle-fill me-1"></i> WhatsApp Activo</span>
                    <span class="badge-status-n8n"><i class="bi bi-diagram-3-fill me-1"></i> n8n Workflows Activo</span>
                    <span class="text-white-50 small">{{ $config['n8n_webhook_base_url'] }}</span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-1">
                    <i class="bi bi-whatsapp text-success me-2"></i>Suite de Automatización WhatsApp & n8n
                </h1>
                <p class="text-white-50 mb-0">Gestione los webhooks oficiales de n8n para el despacho directo de boletas, recordatorios de pago y campañas.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('boletas.morosidad') }}" class="btn btn-outline-light rounded-pill px-3 fw-semibold">
                    <i class="bi bi-shield-exclamation me-1 text-danger"></i> Morosidad
                </a>
                <a href="{{ route('whatsapp.campanas.index') }}" class="btn btn-outline-success rounded-pill px-3 fw-semibold">
                    <i class="bi bi-megaphone me-1"></i> Campañas
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: rgba(37, 211, 102, 0.15); border: 1px solid rgba(37, 211, 102, 0.3); color: #25d366; border-radius: 1rem;">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; border-radius: 1rem;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Formulario de Configuración Principal n8n -->
            <div class="col-lg-7">
                <div class="settings-card">
                    <h4 class="fw-bold text-white mb-2 d-flex align-items-center">
                        <i class="bi bi-diagram-3 text-warning me-2"></i>Webhooks de n8n para WhatsApp
                    </h4>
                    <p class="text-white-50 small mb-4">Los envíos automáticos de boletas, firmas digitales y avisos de cobro se despachan directamente a sus flujos en n8n.</p>

                    <form action="{{ route('configuracion.whatsapp.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Webhook Boletas y Firmas -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 fw-semibold mb-0">Webhook de Boletas y Firmas Digitales</label>
                                <span class="webhook-badge">Boletas</span>
                            </div>
                            <input type="url" name="n8n_webhook_boleta_url" class="form-control form-control-custom" value="{{ old('n8n_webhook_boleta_url', $config['n8n_webhook_boleta_url']) }}" placeholder="https://n8n.renangalvan.net/webhook/cefi-boleta" required>
                            <small class="text-white-50">Se activa al pulsar "Notificar por WhatsApp" en el historial de boletas.</small>
                        </div>

                        <!-- Webhook Morosidad -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 fw-semibold mb-0">Webhook de Cobro de Morosidad</label>
                                <span class="webhook-badge">Morosidad</span>
                            </div>
                            <input type="url" name="n8n_webhook_morosidad_url" class="form-control form-control-custom" value="{{ old('n8n_webhook_morosidad_url', $config['n8n_webhook_morosidad_url']) }}" placeholder="https://n8n.renangalvan.net/webhook/cefi-morosidad" required>
                            <small class="text-white-50">Se activa al notificar mora con recargo de interés compuesto a estudiantes.</small>
                        </div>

                        <!-- Webhook Recordatorio / Avisos Generales -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 fw-semibold mb-0">Webhook de Recordatorios de Cuotas y Pruebas</label>
                                <span class="webhook-badge">Recordatorios</span>
                            </div>
                            <input type="url" name="n8n_webhook_recordatorio_url" class="form-control form-control-custom" value="{{ old('n8n_webhook_recordatorio_url', $config['n8n_webhook_recordatorio_url']) }}" placeholder="https://n8n.renangalvan.net/webhook/cefi-recordatorio">
                            <small class="text-white-50">Flujo n8n para avisos de cuotas próximas a vencer y mensajes generales.</small>
                        </div>

                        <!-- Webhook Campañas Masivas -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 fw-semibold mb-0">Webhook de Campañas Masivas de Difusión</label>
                                <span class="webhook-badge">Marketing</span>
                            </div>
                            <input type="url" name="n8n_webhook_campana_url" class="form-control form-control-custom" value="{{ old('n8n_webhook_campana_url', $config['n8n_webhook_campana_url']) }}" placeholder="https://n8n.renangalvan.net/webhook/cefi-campana">
                            <small class="text-white-50">Orquestador de campañas masivas por lotes hacia prospectos y estudiantes.</small>
                        </div>

                        <!-- Teléfono Administrador -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Teléfono del Administrador / Soporte</label>
                            <input type="text" name="whatsapp_phone" class="form-control form-control-custom" value="{{ old('whatsapp_phone', $config['admin_phone']) }}" placeholder="50687777849">
                            <small class="text-white-50">Número al que se remitirán alertas críticas y pruebas internas.</small>
                        </div>

                        <hr class="border-secondary my-4">

                        <h5 class="fw-bold text-white mb-3">
                            <i class="bi bi-image text-success me-2"></i>Banner y Encabezado Institucional
                        </h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="whatsapp_adjuntar_logo" id="adjuntarLogoSwitch" {{ $config['adjuntar_logo'] ? 'checked' : '' }}>
                            <label class="form-check-label text-white fw-semibold" for="adjuntarLogoSwitch">
                                Adjuntar imagen de encabezado institucional en el payload de n8n
                            </label>
                            <div class="text-white-50 small">Permite que el flujo de n8n incluya la imagen del banner como cabecera del mensaje.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">URL del Logo / Banner</label>
                            <input type="url" name="whatsapp_logo_url" class="form-control form-control-custom" value="{{ old('whatsapp_logo_url', $config['logo_url']) }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">Subir Nuevo Banner (PNG, JPG o WEBP)</label>
                            <input type="file" name="logo_file" class="form-control form-control-custom" accept="image/*">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-wa-save">
                                <i class="bi bi-check2-circle me-1"></i> Guardar Configuración n8n
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel de Pruebas y Estado -->
            <div class="col-lg-5">
                <!-- Tarjeta de Prueba en Vivo -->
                <div class="settings-card mb-4">
                    <h4 class="fw-bold text-white mb-3 d-flex align-items-center">
                        <i class="bi bi-send-check text-success me-2"></i>Prueba de Envío en Vivo vía n8n
                    </h4>
                    <p class="text-white-50 small mb-4">Verifique en tiempo real que su flujo de n8n recibe el payload y entrega el WhatsApp correctamente.</p>

                    <form id="formTestEnvio">
                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Número Destino</label>
                            <input type="text" id="test_destinatario" class="form-control form-control-custom" value="{{ $config['admin_phone'] }}" placeholder="50687777849" required>
                            <small class="text-white-50">Incluya código de país o formato 8 dígitos de CR.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">Mensaje de Prueba</label>
                            <textarea id="test_mensaje" class="form-control form-control-custom" rows="3" required>🏛️ *{{ config('cliente.nombre', 'CEFI') }} - Verificación WhatsApp & n8n*&#10;&#10;Mensaje de prueba despachado exitosamente hacia nuestro flujo de automatización en n8n.</textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-success w-100 py-2 fw-bold">
                            <i class="bi bi-diagram-3-fill me-1"></i> Probar Disparo n8n
                        </button>
                    </form>
                </div>

                <!-- Tarjeta de Vista Previa del Banner -->
                <div class="settings-card">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-eye text-primary me-2"></i>Banner Institucional Activo
                    </h5>
                    <div class="logo-preview-box mb-3">
                        <img src="{{ $config['logo_url'] }}" alt="Logo Banner" id="imgBannerPreview">
                    </div>
                    <div class="text-white-50 small">
                        <i class="bi bi-info-circle me-1"></i> Este banner se adjunta en el payload de las notificaciones institucionales para los flujos de n8n.
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formTestEnvio')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const dest = document.getElementById('test_destinatario').value;
                const msg = document.getElementById('test_mensaje').value;

                Swal.fire({
                    title: 'Enviando WhatsApp...',
                    text: 'Contactando flujo de automatización n8n...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const res = await fetch('{{ route('configuracion.whatsapp.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            destinatario: dest,
                            mensaje: msg
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Despacho Exitoso!',
                            text: data.message + (data.provider ? ' (Proveedor: ' + data.provider + ')' : ''),
                            confirmButtonColor: '#25d366'
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Respuesta de n8n',
                            text: data.message || 'No se pudo entregar el mensaje al webhook de n8n.',
                            confirmButtonColor: '#ff6d5a'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: err.message
                    });
                }
            });
        });
    </script>
@endsection
