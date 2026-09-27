@extends('layouts.app')

@section('title', 'Control de Seguridad - Configuración de la Plataforma')

@section('styles')
<style>
    .auth-container {
        padding-top: 3.5rem;
        padding-bottom: 5rem;
    }

    .auth-card {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        padding: 3rem 2.5rem;
        position: relative;
        overflow: hidden;
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #5fb230 0%, #10b981 50%, #3b82f6 100%);
    }

    .form-control-custom {
        background-color: var(--card-dark) !important;
        border: 2px solid var(--border-dark) !important;
        border-radius: 0.85rem;
        color: #f8fafc !important;
        padding: 0.85rem 1.25rem;
        font-size: 1.15rem;
        transition: all 0.25s ease;
        letter-spacing: 2px;
    }

    [data-theme="light"] .form-control-custom {
        color: #0f172a !important;
        background-color: #ffffff !important;
    }

    .form-control-custom:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.2) !important;
    }

    .shield-icon-wrapper {
        width: 85px;
        height: 85px;
        background: rgba(95, 178, 48, 0.12);
        color: var(--primary);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 0 25px rgba(95, 178, 48, 0.2);
    }
</style>
@endsection

@section('content')
<div class="container auth-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Control de Seguridad de Plataforma</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card text-center">
                <div class="shield-icon-wrapper">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 mb-2 font-monospace small">
                    <i class="bi bi-lock-fill me-1"></i> ÁREA RESTRINGIDA
                </span>

                <h3 class="fw-bold mb-2 text-white">Configuración del Sistema</h3>
                <p class="text-white-50 small mb-4">
                    Este panel permite programar parámetros institucionales, identidades, webhooks n8n y módulos del sistema. Ingrese su clave superior de Administrador General para desbloquear la sesión.
                </p>

                @if (session('warning'))
                    <div class="alert alert-warning py-2 small border-0 rounded-3 mb-4 shadow-sm text-start" style="background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3) !important;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('warning') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger py-2 small border-0 rounded-3 mb-4 shadow-sm text-start" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('configuracion.acceder') }}">
                    @csrf
                    <div class="mb-4 text-start">
                        <label class="form-label text-muted fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Clave de Seguridad Superior</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="clave_acceso" id="clave_acceso" class="form-control form-control-custom border-start-0 border-end-0 text-center" placeholder="••••••••••••" required autofocus autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePasswordBtn" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-unlock-fill"></i> Autorizar y Desbloquear
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar y volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('clave_acceso');
        const icon = document.getElementById('toggleIcon');
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
</script>
@endsection
