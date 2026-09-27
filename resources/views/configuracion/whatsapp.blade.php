@extends('layouts.app')

@section('title', 'Inbox BPM - Configuración de WhatsApp y Green-API')

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
    </style>
@endsection

@section('content')
    <div class="container py-5">

        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-status-online"><i class="bi bi-broadcast me-1"></i> Green-API Activo</span>
                    <span class="text-white-50 small">Instancia: {{ $config['instance'] }}</span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-1">
                    <i class="bi bi-whatsapp text-success me-2"></i>Suite de Automatización WhatsApp & n8n
                </h1>
                <p class="text-white-50 mb-0">Gestione credenciales de Green-API, personalización del banner institucional y envíos directos.</p>
            </div>
            <div>
                <a href="{{ route('boletas.morosidad') }}" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                    <i class="bi bi-shield-exclamation me-1 text-danger"></i> Ir a Morosidad
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
            <!-- Formulario de Configuración Principal -->
            <div class="col-lg-7">
                <div class="settings-card">
                    <h4 class="fw-bold text-white mb-4 d-flex align-items-center">
                        <i class="bi bi-sliders text-success me-2"></i>Parámetros de Green-API
                    </h4>

                    <form action="{{ route('configuracion.whatsapp.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">URL de la API</label>
                            <input type="url" name="green_api_url" class="form-control form-control-custom" value="{{ old('green_api_url', $config['url']) }}" required>
                            <small class="text-white-50">Generalmente <code>https://7107.api.greenapi.com</code></small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 fw-semibold">ID de Instancia</label>
                                <input type="text" name="green_api_instance" class="form-control form-control-custom" value="{{ old('green_api_instance', $config['instance']) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 fw-semibold">Teléfono Administrador</label>
                                <input type="text" name="whatsapp_phone" class="form-control form-control-custom" value="{{ old('whatsapp_phone', $config['admin_phone']) }}" placeholder="50687777849">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">Token de API (ApiTokenInstance)</label>
                            <input type="password" name="green_api_token" class="form-control form-control-custom" value="{{ old('green_api_token', $config['token']) }}" required>
                        </div>

                        <hr class="border-secondary my-4">

                        <h5 class="fw-bold text-white mb-3">
                            <i class="bi bi-image text-warning me-2"></i>Banner y Encabezado Institucional
                        </h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="whatsapp_adjuntar_logo" id="adjuntarLogoSwitch" {{ $config['adjuntar_logo'] ? 'checked' : '' }}>
                            <label class="form-check-label text-white fw-semibold" for="adjuntarLogoSwitch">
                                Adjuntar imagen de encabezado institucional en avisos de cobro y boletas
                            </label>
                            <div class="text-white-50 small">Envía el mensaje como pie de foto (caption), garantizando fondo blanco sólido y aspecto institucional.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">URL del Logo / Banner</label>
                            <input type="url" name="whatsapp_logo_url" class="form-control form-control-custom" value="{{ old('whatsapp_logo_url', $config['logo_url']) }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">Subir Nuevo Banner (PNG, JPG o WEBP)</label>
                            <input type="file" name="logo_file" class="form-control form-control-custom" accept="image/*">
                        </div>

                        <hr class="border-secondary my-4">

                        <h5 class="fw-bold text-white mb-3">
                            <i class="bi bi-diagram-3 text-info me-2"></i>Webhook Secundario n8n (Opcional)
                        </h5>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">URL del Webhook de Respaldo n8n</label>
                            <input type="url" name="n8n_webhook_recordatorio_url" class="form-control form-control-custom" value="{{ old('n8n_webhook_recordatorio_url', $config['n8n_webhook']) }}" placeholder="https://n8n.tudominio.com/webhook/recordatorio">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-wa-save">
                                <i class="bi bi-check2-circle me-1"></i> Guardar Cambios
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
                        <i class="bi bi-send-check text-success me-2"></i>Prueba de Envío en Vivo
                    </h4>
                    <p class="text-white-50 small mb-4">Verifique en tiempo real que su instancia de Green-API responde y entrega mensajes instantáneamente.</p>

                    <form id="formTestEnvio">
                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Número Destino</label>
                            <input type="text" id="test_destinatario" class="form-control form-control-custom" value="{{ $config['admin_phone'] }}" placeholder="50687777849" required>
                            <small class="text-white-50">Incluya código de país o formato 8 dígitos CR.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 fw-semibold">Mensaje de Prueba</label>
                            <textarea id="test_mensaje" class="form-control form-control-custom" rows="3" required>🏛️ *{{ config('cliente.nombre', 'CEFI') }} - Prueba de Conexión WhatsApp*&#10;&#10;Este es un mensaje de verificación enviado exitosamente desde Inbox BPM (Laravel).</textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-success w-100 py-2 fw-bold">
                            <i class="bi bi-whatsapp me-1"></i> Disparar Mensaje de Prueba
                        </button>
                    </form>
                </div>

                <!-- Tarjeta de Vista Previa del Banner -->
                <div class="settings-card">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-eye text-primary me-2"></i>Vista Previa del Banner Activo
                    </h5>
                    <div class="logo-preview-box mb-3">
                        <img src="{{ $config['logo_url'] }}" alt="Logo Banner" id="imgBannerPreview">
                    </div>
                    <div class="text-white-50 small">
                        <i class="bi bi-info-circle me-1"></i> Este banner se adjunta automáticamente en la parte superior de cada aviso de cobro o boleta.
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
                    text: 'Contactando con Green-API',
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
                            title: '¡Mensaje Enviado!',
                            text: data.message + (data.provider ? ' (Proveedor: ' + data.provider + ')' : ''),
                            confirmButtonColor: '#25d366'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Envío',
                            text: data.message || 'No se pudo entregar el mensaje.'
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
