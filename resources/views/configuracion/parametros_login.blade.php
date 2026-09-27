@extends('layouts.app')

@section('title', 'Control de Seguridad - Parámetros del Sistema')

@section('styles')
<style>
    .auth-container {
        padding-top: 3rem;
        padding-bottom: 5rem;
    }

    .auth-card {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 3rem 2.5rem;
    }

    .form-control-custom {
        background-color: var(--card-dark) !important;
        border: 2px solid var(--border-dark) !important;
        border-radius: 0.85rem;
        color: #f8fafc !important;
        padding: 0.85rem 1.25rem;
        font-size: 1.1rem;
        transition: all 0.25s ease;
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
        width: 80px;
        height: 80px;
        background: rgba(95, 178, 48, 0.12);
        color: var(--primary);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container auth-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Control de Seguridad</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card text-center">
                <div class="shield-icon-wrapper">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                
                <h3 class="fw-bold mb-2 text-white">Control de Acceso Superior</h3>
                <p class="text-white-50 small mb-4">
                    Esta sección modifica la identidad, logotipo y parámetros estructurales del sistema. Ingrese la clave de autorización para continuar.
                </p>

                @if (session('error'))
                    <div class="alert alert-danger py-2 small border-0 rounded-3 mb-4 shadow-sm" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('configuracion.parametros.acceder') }}">
                    @csrf
                    <div class="mb-4 text-start">
                        <label class="form-label text-muted fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Clave de Seguridad Superior</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0" style="background-color: var(--card-dark); border-color: var(--border-dark); color: var(--text-muted);"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="clave_acceso" id="clave_acceso" class="form-control form-control-custom border-start-0 text-center" placeholder="••••••••••••" required autofocus>
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
