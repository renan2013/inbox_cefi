@extends('layouts.app')

@section('title', 'Inbox BPM - Configuración de Alertas')

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

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.9rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.25);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Configuración de Alertas</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Header -->
                <div class="page-header">
                    <h1 class="display-6 fw-bold mb-1">Configuración de Alertas</h1>
                    <p class="text-white-50 mb-0">Defina los canales y destinatarios de notificaciones automáticas y avisos por mora de WhatsApp.</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <!-- GUIA WHATSAPP -->
                <div class="card glass-card border-start border-4 border-success overflow-hidden mb-4">
                    <div class="card-body p-4 text-white">
                        <h5 class="fw-bold text-success mb-3"><i class="bi bi-info-circle-fill"></i> ¿Cómo activar las alertas de WhatsApp?</h5>
                        <p class="small text-white mb-4">Siga estos pasos obligatorios para asociar su cuenta y recibir los reportes:</p>
                        
                        <div class="d-flex align-items-start mb-3 text-white">
                            <div class="badge bg-success rounded-circle me-3">1</div>
                            <div>
                                <strong class="text-success">Iniciar Chat:</strong> Presione el botón verde de abajo para abrir el chat del Bot oficial de WhatsApp.
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 text-white">
                            <div class="badge bg-success rounded-circle me-3">2</div>
                            <div>
                                <strong class="text-success">Enviar y Guardar:</strong> Envíe el texto predefinido que aparece en pantalla y agregue el número <strong>+34 621 371 153</strong> como "Inbox Bot" en sus contactos.
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4 text-white">
                            <div class="badge bg-success rounded-circle me-3">3</div>
                            <div>
                                <strong class="text-success">Copiar API Key:</strong> Copie el código de 7 dígitos que el bot le enviará de respuesta y péguelo en el campo correspondiente abajo.
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="https://wa.me/34621371153?text=I%20allow%20callmebot%20to%20call%20me" target="_blank" class="btn btn-success fw-bold px-4 py-2 shadow">
                                <i class="bi bi-whatsapp me-2"></i> Abrir WhatsApp para Vincular
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CONFIG FORM -->
                <div class="card glass-card">
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('configuracion.alertas.store') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="whatsapp_phone" class="form-label-custom">Número de WhatsApp Destinatario</label>
                                <input type="text" name="whatsapp_phone" id="whatsapp_phone" class="form-control form-control-custom w-100" placeholder="Ej: 50688887777" value="{{ old('whatsapp_phone', $config['whatsapp_phone']) }}">
                                <div class="form-text text-white-50 small mt-1">Código de país seguido del número telefónico sin espacios ni guiones.</div>
                            </div>

                            <div class="mb-4">
                                <label for="whatsapp_api_key" class="form-label-custom">WhatsApp API Key (CallMeBot)</label>
                                <input type="text" name="whatsapp_api_key" id="whatsapp_api_key" class="form-control form-control-custom w-100" placeholder="Ej: 1234567" value="{{ old('whatsapp_api_key', $config['whatsapp_api_key']) }}">
                            </div>

                            <div class="mb-5">
                                <label for="inventario_alert_email" class="form-label-custom">Correo Destinatario de Reportes</label>
                                <input type="email" name="inventario_alert_email" id="inventario_alert_email" class="form-control form-control-custom w-100" placeholder="ejemplo@unela.ac.cr" value="{{ old('inventario_alert_email', $config['inventario_alert_email']) }}">
                            </div>

                            <button type="submit" class="btn btn-submit w-100 py-3 shadow">
                                <i class="bi bi-save me-2"></i> Guardar Configuración de Alertas
                            </button>

                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
