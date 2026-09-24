@extends('layouts.app')

@section('title', 'Inbox BPM - Área Restringida')

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
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.2);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Utilitarios Restringidos</li>
            </ol>
        </nav>

        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card glass-card p-5 text-center">
                    <i class="bi bi-shield-lock text-success display-3 mb-4"></i>
                    <h4 class="fw-bold mb-2">Área Restringida</h4>
                    <p class="text-white-50 small mb-4">Por favor, ingrese la clave de acceso maestra para continuar con el diagnóstico.</p>
                    
                    @if (session('error'))
                        <div class="alert alert-danger py-2 small border-0 rounded-3 mb-3" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('utilitarios.acceder') }}">
                        @csrf
                        <input type="password" name="clave_acceso" class="form-control form-control-custom text-center mb-4" placeholder="Clave Maestra" required autofocus>
                        <button type="submit" class="btn btn-submit w-100 py-3 shadow">
                            <i class="bi bi-unlock-fill me-2"></i> Desbloquear Panel
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
