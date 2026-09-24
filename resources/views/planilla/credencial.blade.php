@extends('layouts.app')

@section('title', 'Inbox BPM - Mi Credencial QR Virtual')

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

        .credencial-container {
            max-width: 450px;
            margin: 2rem auto;
        }

        .credencial-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .credencial-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(95, 178, 48, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .avatar-large {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: 0 0 20px rgba(95, 178, 48, 0.3);
            margin: 0 auto 1.5rem auto;
            background-color: var(--card-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .qr-wrapper {
            background-color: white;
            padding: 1.25rem;
            border-radius: 1rem;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }

        .logo-text {
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Mi Credencial QR</li>
            </ol>
        </nav>

        <div class="credencial-container">
            <div class="credencial-card">
                
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <span class="logo-text"><i class="bi bi-mortarboard-fill me-1"></i> UNELA</span>
                    <span class="badge bg-success bg-opacity-20 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25">CREDENTIAL</span>
                </div>

                <!-- PROFILE IMAGE -->
                @if ($user_data->foto_credencial)
                    <img src="{{ asset($user_data->foto_credencial) }}" class="avatar-large" alt="Foto">
                @else
                    <div class="avatar-large">
                        {{ strtoupper(substr($user_data->nombre, 0, 1)) }}
                    </div>
                @endif

                <h4 class="fw-bold text-white mb-1">{{ $user_data->nombre }} {{ $user_data->apellidos }}</h4>
                <p class="text-success fw-bold small mb-4" style="letter-spacing: 0.5px; text-transform: uppercase;">
                    {{ $user_data->cargo_credencial ?: ($user_data->rol_nombre ?: 'Colaborador') }}
                </p>

                <!-- QR CODE -->
                <div class="qr-wrapper">
                    <div id="qrcode"></div>
                </div>

                <div class="border-top border-secondary pt-4 mt-2 text-start row g-3 text-white-50 small">
                    <div class="col-6">
                        <span class="d-block" style="font-size: 0.7rem; text-transform: uppercase;">Identificación:</span>
                        <strong class="text-white">{{ $user_data->cedula ?: 'No asignada' }}</strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="d-block" style="font-size: 0.7rem; text-transform: uppercase;">Vence:</span>
                        @if ($vencido)
                            <strong class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> VENCIDA</strong>
                        @else
                            <strong class="text-white">{{ Carbon\Carbon::parse($user_data->vigencia_credencial)->format('d/m/Y') }}</strong>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        $(document).ready(function() {
            const payload = '{!! $qr_payload !!}';
            
            new QRCode(document.getElementById("qrcode"), {
                text: payload,
                width: 150,
                height: 150,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    </script>
@endsection
