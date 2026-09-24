@extends('layouts.app')

@section('title', 'Inbox BPM - Estación de Asistencia QR')

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

        .scanner-view {
            position: relative;
            background: #000;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 4px solid var(--border-dark);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            aspect-ratio: 4/3;
            max-width: 500px;
            margin: 0 auto;
        }

        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .scanner-laser {
            position: absolute;
            width: 80%;
            height: 4px;
            background-color: #ef4444;
            box-shadow: 0 0 15px #ef4444, 0 0 5px #ef4444;
            animation: laser-scan 2.5s infinite ease-in-out;
        }

        @keyframes laser-scan {
            0%, 100% { top: 15%; }
            50% { top: 85%; }
        }

        .scanner-target {
            width: 60%;
            height: 60%;
            border: 3px dashed var(--primary);
            border-radius: 1.5rem;
            box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.4);
            position: relative;
            animation: target-glow 2s infinite alternate;
        }

        @keyframes target-glow {
            from { border-color: rgba(95, 178, 48, 0.5); }
            to { border-color: rgba(95, 178, 48, 1); box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5), 0 0 20px rgba(95, 178, 48, 0.3); }
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            height: 100%;
        }

        .feedback-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.03);
            border: 2px solid var(--border-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem auto;
            color: var(--primary);
        }

        .feedback-badge {
            font-size: 1.1rem;
            font-weight: 800;
            padding: 0.5rem 2rem;
            border-radius: 50px;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planilla.index') }}" class="text-decoration-none text-white-50">Panel de Planilla</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Estación Asistencia QR</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-6 fw-bold mb-1">Estación de Marcación QR</h1>
            <p class="text-white-50 mb-0">Coloque la credencial QR del empleado frente a la cámara para registrar entrada o salida.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- CAMERA SCREEN -->
            <div class="col-lg-6">
                <div class="card glass-card p-4">
                    <div class="scanner-view" id="reader">
                        <div class="scanner-overlay">
                            <div class="scanner-laser"></div>
                            <div class="scanner-target"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ASYNCHRONOUS FEEDBACK -->
            <div class="col-lg-6">
                <div class="card glass-card p-5 text-center d-flex flex-column justify-content-center" id="feedback-panel">
                    <div id="feedback-idle">
                        <i class="bi bi-camera-fill text-white-50 display-3 mb-3"></i>
                        <h4 class="fw-bold text-white">Esperando Escaneo...</h4>
                        <p class="text-white-50 small mb-0">La estación se encuentra activa. Acerque un código QR válido para registrar la marca.</p>
                    </div>
                    
                    <div id="feedback-result" class="d-none animate__animated animate__fadeIn">
                        <div class="feedback-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-1" id="result-name">Nombre Colaborador</h4>
                        <p class="text-white-50 small mb-4" id="result-email">email@unela.ac.cr</p>
                        
                        <div class="mb-3">
                            <div class="feedback-badge" id="result-badge">Marcado Exitoso</div>
                        </div>
                        
                        <p class="text-white fw-bold mb-0" id="result-time">12:00:00 PM</p>
                        <span class="text-white-50 small d-block mt-1" id="result-date">Domingo, 9 de Agosto</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let html5QrcodeScanner = new Html5Qrcode("reader");
            let lastScanTime = 0;
            let isProcessing = false;

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            html5QrcodeScanner.start({ facingMode: "user" }, config, onScanSuccess)
                .catch(err => {
                    console.error("Error al iniciar cámara: ", err);
                    Swal.fire('Error', 'No se pudo acceder a la cámara. Asegúrese de otorgar los permisos.', 'error');
                });

            function onScanSuccess(decodedText, decodedResult) {
                const now = Date.now();
                if (now - lastScanTime < 3000 || isProcessing) return; // Esperar 3 segundos entre marcas
                
                isProcessing = true;
                lastScanTime = now;

                try {
                    const data = JSON.parse(decodedText);
                    if (data.id && data.token) {
                        procesarMarcaServidor(data.id, data.token);
                    } else {
                        showScanError("El código QR escaneado no tiene un formato válido.");
                    }
                } catch(e) {
                    showScanError("El código QR escaneado no es válido para este sistema.");
                }
            }

            function procesarMarcaServidor(id, token) {
                $.ajax({
                    url: "{{ route('planilla.escanear.marca') }}",
                    type: "POST",
                    data: {
                        id_usuario: id,
                        token: token,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        isProcessing = false;
                        if (response.success) {
                            showScanSuccess(response);
                        } else {
                            showScanError(response.message);
                        }
                    },
                    error: function() {
                        isProcessing = false;
                        showScanError("Error de conectividad con el servidor.");
                    }
                });
            }

            function showScanSuccess(res) {
                $('#feedback-idle').addClass('d-none');
                $('#feedback-result').removeClass('d-none');

                $('#result-name').text(res.empleado.nombre);
                $('#result-email').text(res.empleado.email);
                $('#result-time').text(res.marca.hora);
                $('#result-date').text(res.marca.fecha);

                const badge = $('#result-badge').removeClass('bg-success bg-opacity-20 text-success bg-danger bg-opacity-20 text-danger border border-success border-danger');
                if (res.marca.tipo === 'Entrada') {
                    badge.addClass('bg-success bg-opacity-20 text-success border border-success border-opacity-25').text('Entrada Registrada');
                } else {
                    badge.addClass('bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25').text('Salida Registrada');
                }

                // Audio cue
                const audio = new Audio("https://assets.mixkit.co/active_storage/sfx/2869/2869-200.wav");
                audio.play();

                setTimeout(() => {
                    $('#feedback-result').addClass('d-none');
                    $('#feedback-idle').removeClass('d-none');
                }, 4000);
            }

            function showScanError(msg) {
                isProcessing = false;
                Swal.fire({
                    title: 'Acceso Rechazado',
                    text: msg,
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#fff'
                });
                
                // Audio error cue
                const audio = new Audio("https://assets.mixkit.co/active_storage/sfx/863/863-200.wav");
                audio.play();
            }
        });
    </script>
@endsection
