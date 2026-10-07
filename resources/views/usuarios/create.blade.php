@extends('layouts.app')

@section('title', 'Inbox BPM - Registrar Nuevo Usuario')

@section('styles')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
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

        .success-card {
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.3);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2.5rem;
        }

        .copy-badge {
            cursor: pointer;
            transition: all 0.2s;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 50px;
            font-family: 'Consolas', monospace;
            font-size: 0.9rem;
            font-weight: 700;
            border: 1px solid rgba(95, 178, 48, 0.3);
            display: inline-block;
        }

        .copy-badge:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-1px);
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.8rem;
            border-radius: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.25);
        }

        .btn-secondary-custom {
            background-color: transparent;
            border: 2px solid var(--border-dark);
            color: var(--text-muted);
            padding: 0.4rem 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-secondary-custom:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" class="text-decoration-none text-white-50">Usuarios</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Registro</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8">
                
                <!-- Success Details Card (from Session) -->
                @if (session('new_user_details'))
                    @php $details = session('new_user_details'); @endphp
                    <div class="success-card text-center animate__animated animate__fadeIn">
                        <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i></div>
                        <h4 class="text-success fw-bold">¡Registro Exitoso!</h4>
                        <p class="text-white-50 small mb-4">Copia estos datos y compártelos con el usuario. <b>Esta información no volverá a mostrarse.</b></p>
                        
                        <div class="bg-dark p-4 rounded-4 border border-secondary shadow-sm text-start mx-auto" style="max-width: 450px;">
                            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="small text-white-50 fw-bold text-uppercase">Nombre:</span>
                                <span class="fw-bold text-white">{{ $details['nombre'] }} {{ $details['apellidos'] }}</span>
                            </div>
                            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="small text-white-50 fw-bold text-uppercase">Correo:</span>
                                <span class="copy-badge" onclick="copyText('{{ $details['email'] }}', 'Correo')" title="Click para copiar">
                                    {{ $details['email'] }} <i class="bi bi-clipboard ms-1"></i>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="small text-white-50 fw-bold text-uppercase">Contraseña:</span>
                                <span class="copy-badge" onclick="copyText('{{ $details['password'] }}', 'Contraseña')" title="Click para copiar">
                                    {{ $details['password'] }} <i class="bi bi-clipboard ms-1"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Warning Banner (Oculto: Sincronización Moodle no contratada por este cliente) -->
                <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-start mb-4 text-start d-none" style="display: none !important; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2) !important; color: #fbbf24;">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
                    <div>
                        <strong>Sincronización con Moodle:</strong> Si necesita registrar un nuevo estudiante para sincronizarlo con Moodle, el punto de partida siempre será crear el usuario directamente en <strong><a href="{{ config('cliente.campus_virtual', '#') }}" target="_blank" style="color: inherit; text-decoration: underline;">{{ config('cliente.nombre', 'CEFI') }} Virtual</a></strong>. Este formulario es exclusivo para registrar usuarios directamente en el sistema local de Inbox.
                    </div>
                </div>

                <!-- Registration Card -->
                <div class="card glass-card">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="mb-4 text-center">
                            <h2 class="fw-bold text-white">Registrar Nuevo Usuario</h2>
                            <p class="text-white-50">Cree credenciales locales para estudiantes, profesores o administradores de la plataforma.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <form action="{{ route('usuarios.store') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="nombre" class="form-label form-label-custom"><i class="bi bi-person"></i> Nombres</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" placeholder="Ej: Juan" value="{{ old('nombre') }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="apellidos" class="form-label form-label-custom"><i class="bi bi-person"></i> Apellidos</label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control form-control-custom" placeholder="Ej: Pérez García" value="{{ old('apellidos') }}" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="cedula" class="form-label form-label-custom"><i class="bi bi-card-text"></i> Identificación</label>
                                <input type="text" name="cedula" id="cedula" class="form-control form-control-custom" placeholder="Formato nacional o pasaporte" value="{{ old('cedula') }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label form-label-custom"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control form-control-custom" placeholder="nombre@ejemplo.com" value="{{ old('email') }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="telefono" class="form-label form-label-custom"><i class="bi bi-whatsapp"></i> Teléfono / WhatsApp</label>
                                <input type="text" name="telefono" id="telefono" class="form-control form-control-custom" placeholder="Ej: 50688889999" value="{{ old('telefono') }}">
                                <div class="form-text text-white-50">Incluya el código de país (ej: 506 para CR).</div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label form-label-custom"><i class="bi bi-shield-lock"></i> Contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control form-control-custom border-end-0" placeholder="Escriba o genere una" required>
                                    <button class="btn btn-outline-secondary border-start-0 px-3 border-2" type="button" id="togglePasswordVisibility" style="border-radius: 0 0.75rem 0.75rem 0; border-color: var(--border-dark); background-color: rgba(255,255,255,0.02);">
                                        <i class="bi bi-eye-slash text-white-50"></i>
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-secondary-custom" type="button" id="generatePasswordBtn">
                                        <i class="bi bi-magic me-1"></i> Generar contraseña segura
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-submit">
                                    <i class="bi bi-person-plus-fill me-2"></i> Registrar Usuario
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('usuarios.index') }}" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left me-1"></i> Volver al listado de usuarios
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function copyText(text, label) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: label + ' copiado',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const generateBtn = document.getElementById('generatePasswordBtn');
            const togglePasswordBtn = document.getElementById('togglePasswordVisibility');
            const togglePasswordIcon = togglePasswordBtn.querySelector('i');

            generateBtn.addEventListener('click', function() {
                const generatedPassword = generateRandomPassword();
                passwordInput.value = generatedPassword;
                passwordInput.type = "text";
                togglePasswordIcon.classList.replace('bi-eye-slash', 'bi-eye');
                
                Swal.fire({
                    toast: true,
                    position: 'center',
                    icon: 'info',
                    title: 'Contraseña generada',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    togglePasswordIcon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    togglePasswordIcon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });

            function generateRandomPassword(length = 12) {
                const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%&*-_=+";
                let password = "";
                for (let i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                return password;
            }
        });
    </script>
@endsection
