@extends('layouts.app')

@section('title', 'Inbox BPM - Autorización Bóveda de Claves')

@section('styles')
    <style>
        .auth-container {
            max-width: 450px;
            margin: 5rem auto;
        }

        .auth-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.9rem 1.2rem;
            text-align: center;
            font-size: 1.25rem;
            letter-spacing: 0.1em;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
            outline: none;
        }

        .lock-icon {
            font-size: 3rem;
            color: #ef4444;
            text-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <div class="auth-container">
            <div class="auth-card">
                
                <i class="bi bi-shield-lock-fill lock-icon animate__animated animate__pulse animate__infinite"></i>
                
                <h3 class="fw-bold text-white mb-2">Bóveda de Claves</h3>
                <p class="text-white-50 small mb-4">Esta es una sección altamente protegida. Introduzca la clave maestra para consultar y editar las credenciales institucionales.</p>

                @if (session('error_auth'))
                    <div class="alert alert-danger border-0 rounded-4 mb-4 small text-start" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error_auth') }}
                    </div>
                @endif

                <form action="{{ route('claves.acceder') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <input type="password" name="clave_acceso_maestra" class="form-control form-control-custom" placeholder="••••••••" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-3 rounded-3 fw-bold" style="background-color: #ef4444; border: none; transition: all 0.3s;">
                        <i class="bi bi-unlock-fill me-1"></i> Desbloquear Bóveda
                    </button>
                </form>

            </div>
        </div>
    </div>
@endsection
