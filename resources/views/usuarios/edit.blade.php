@extends('layouts.app')

@section('title', 'Inbox BPM - Editar Usuario')

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

        [data-theme="light"] .page-header {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        [data-theme="light"] .glass-card {
            background-color: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .form-control-custom,
        .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        [data-theme="light"] .form-control-custom,
        [data-theme="light"] .form-select-custom {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus,
        [data-theme="light"] .form-select-custom:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15) !important;
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
            color: white;
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
                <li class="breadcrumb-item active text-white" aria-current="page">Editar Usuario</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8">
                
                @if (session('success'))
                    <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4 shadow-sm" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
                        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <!-- Edit Card -->
                <div class="card glass-card">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="mb-4 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 p-3" style="background: rgba(95, 178, 48, 0.15); width: 64px; height: 64px;">
                                <i class="bi bi-person-gear text-primary fs-2"></i>
                            </div>
                            <h2 class="fw-bold text-white mb-1">Editar Usuario</h2>
                            <p class="text-white-50 mb-0">Modifique los datos institucionales, contacto y credenciales de acceso.</p>
                            <div class="mt-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 font-monospace">
                                    ID: {{ str_pad($usuario->id, 4, '0', STR_PAD_LEFT) }} &bull; Origen: {{ strtoupper($usuario->origen ?? 'INBOX') }}
                                </span>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <form action="{{ route('usuarios.update', $usuario->id) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="nombre" class="form-label form-label-custom"><i class="bi bi-person"></i> Nombres</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" placeholder="Ej: Juan" value="{{ old('nombre', $usuario->nombre) }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="apellidos" class="form-label form-label-custom"><i class="bi bi-person"></i> Apellidos</label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control form-control-custom" placeholder="Ej: Pérez García" value="{{ old('apellidos', $usuario->apellidos) }}" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="cedula" class="form-label form-label-custom"><i class="bi bi-card-text"></i> Identificación / Cédula</label>
                                <input type="text" name="cedula" id="cedula" class="form-control form-control-custom" placeholder="Formato nacional o pasaporte" value="{{ old('cedula', $usuario->cedula) }}">
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label form-label-custom"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control form-control-custom" placeholder="nombre@ejemplo.com" value="{{ old('email', $usuario->email) }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="telefono" class="form-label form-label-custom"><i class="bi bi-whatsapp"></i> Teléfono / WhatsApp</label>
                                <input type="text" name="telefono" id="telefono" class="form-control form-control-custom" placeholder="Ej: 50688889999" value="{{ old('telefono', $usuario->telefono) }}">
                                <div class="form-text text-white-50">Incluya el código de país (ej: 506 para Costa Rica).</div>
                            </div>

                            <div class="mb-4">
                                <label for="id_rol" class="form-label form-label-custom"><i class="bi bi-shield-check"></i> Rol Institucional</label>
                                <select name="id_rol" id="id_rol" class="form-select form-select-custom" required>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id }}" {{ old('id_rol', $usuario->id_rol) == $rol->id ? 'selected' : '' }}>
                                            {{ $rol->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label form-label-custom"><i class="bi bi-shield-lock"></i> Contraseña Nueva (Opcional)</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control form-control-custom border-end-0" placeholder="Dejar en blanco para conservar la actual">
                                    <button class="btn btn-outline-secondary border-start-0 px-3 border-2" type="button" id="togglePasswordVisibility" style="border-radius: 0 0.75rem 0.75rem 0; border-color: var(--border-dark); background-color: rgba(255,255,255,0.02);">
                                        <i class="bi bi-eye-slash text-white-50"></i>
                                    </button>
                                </div>
                                <div class="form-text text-white-50">Solo escriba si desea cambiar la contraseña del usuario. Mínimo 6 caracteres.</div>
                                <div class="mt-2">
                                    <button class="btn btn-secondary-custom" type="button" id="generatePasswordBtn">
                                        <i class="bi bi-magic me-1"></i> Generar contraseña segura
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 gap-3">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-submit px-5 rounded-pill shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('togglePasswordVisibility');
        const passwordInput = document.getElementById('password');
        const generateBtn = document.getElementById('generatePasswordBtn');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }
            });
        }

        if (generateBtn && passwordInput) {
            generateBtn.addEventListener('click', function() {
                const chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*";
                let pass = "";
                for (let i = 0; i < 10; i++) {
                    pass += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                passwordInput.value = pass;
                passwordInput.setAttribute('type', 'text');
                const icon = toggleBtn ? toggleBtn.querySelector('i') : null;
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Contraseña generada: ' + pass,
                    showConfirmButton: false,
                    timer: 3500
                });
            });
        }
    });
</script>
@endsection
