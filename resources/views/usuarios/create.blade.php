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

        /* --- MODO CLARO / LIGHT THEME --- */
        [data-theme="light"] .glass-card {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .form-label-custom {
            color: #1e293b !important;
        }

        [data-theme="light"] .form-control-custom {
            background-color: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus {
            background-color: #ffffff !important;
            border-color: var(--primary, #5fb230) !important;
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15) !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .btn-secondary-custom {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }

        [data-theme="light"] .btn-secondary-custom:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        [data-theme="light"] #togglePasswordVisibility {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        [data-theme="light"] #togglePasswordVisibility i {
            color: #64748b !important;
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

        <div class="row">
            <div class="col-12">
                
                <!-- Success Details Card (from Session) -->
                @if (session('new_user_details'))
                    @php
                        $details = session('new_user_details');
                        $institucionNombre = \App\Services\ClienteService::nombre();
                        $plataformaUrl = url('/');
                        $nombreCompleto = trim(($details['nombre'] ?? '') . ' ' . ($details['apellidos'] ?? ''));
                        $mensajeDefault = "¡Hola {$nombreCompleto}! 👋\n\nYa tienes acceso a la plataforma *{$institucionNombre}*.\n\n🔗 *Enlace de ingreso:* {$plataformaUrl}\n📧 *Correo:* {$details['email']}\n🔑 *Contraseña:* {$details['password']}\n\nTe recomendamos guardar tus credenciales y cambiar tu contraseña al ingresar.";
                    @endphp
                    <div class="success-card text-center animate__animated animate__fadeIn">
                        <div class="mb-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i></div>
                        <h4 class="text-success fw-bold">¡Registro Exitoso!</h4>
                        <p class="text-white-50 small mb-4">Edita, copia o envía directamente por WhatsApp la tarjeta de acceso. <b>La contraseña no volverá a mostrarse.</b></p>

                        <div class="bg-dark p-4 rounded-4 border border-secondary shadow-sm text-start mx-auto" style="max-width: 620px;">
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <span class="small text-white-50 fw-bold text-uppercase d-block">Nombre:</span>
                                    <span class="fw-bold text-white">{{ $nombreCompleto }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="small text-white-50 fw-bold text-uppercase d-block">Enlace Plataforma:</span>
                                    <span class="copy-badge mt-1" onclick="copyText('{{ $plataformaUrl }}', 'Enlace')" title="Click para copiar">
                                        {{ $plataformaUrl }} <i class="bi bi-clipboard ms-1"></i>
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="small text-white-50 fw-bold text-uppercase d-block">Correo:</span>
                                    <span class="copy-badge mt-1" onclick="copyText('{{ $details['email'] }}', 'Correo')" title="Click para copiar">
                                        {{ $details['email'] }} <i class="bi bi-clipboard ms-1"></i>
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="small text-white-50 fw-bold text-uppercase d-block">Contraseña:</span>
                                    <span class="copy-badge mt-1" onclick="copyText('{{ $details['password'] }}', 'Contraseña')" title="Click para copiar">
                                        {{ $details['password'] }} <i class="bi bi-clipboard ms-1"></i>
                                    </span>
                                </div>
                            </div>

                            <hr class="border-secondary opacity-25 my-3">

                            <div class="mb-3">
                                <label for="wa_target_phone" class="form-label small text-white-50 fw-bold text-uppercase mb-1">
                                    <i class="bi bi-whatsapp text-success me-1"></i> Teléfono WhatsApp del Usuario
                                </label>
                                <input type="text" id="wa_target_phone" class="form-control form-control-sm form-control-custom"
                                       value="{{ $details['telefono'] ?? '' }}" placeholder="Ej: 50688887777">
                            </div>

                            <div class="mb-3">
                                <label for="wa_editable_card" class="form-label small text-white-50 fw-bold text-uppercase mb-1">
                                    <i class="bi bi-pencil-square text-info me-1"></i> Tarjeta de Bienvenida (Editable)
                                </label>
                                <textarea id="wa_editable_card" rows="7" class="form-control form-control-custom" style="font-size: 0.9rem; line-height: 1.45;">{{ $mensajeDefault }}</textarea>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-semibold" onclick="copiarTarjetaCompleta()">
                                    <i class="bi bi-clipboard-check me-1"></i> Copiar Tarjeta
                                </button>
                                <button type="button" id="btnEnviarEmailCreds" class="btn btn-info btn-sm rounded-pill px-3 fw-semibold text-white" onclick="enviarCredencialesPorCorreo({{ json_encode($details['email'] ?? '') }}, {{ json_encode(trim(($details['nombre'] ?? '') . ' ' . ($details['apellidos'] ?? ''))) }})">
                                    <i class="bi bi-envelope-check-fill me-1"></i> Enviar por Correo (SMTP)
                                </button>
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold" onclick="abrirWhatsAppTarjeta()">
                                    <i class="bi bi-whatsapp me-1"></i> Enviar por WhatsApp
                                </button>
                                <button type="button" id="btnAutoWaCreds" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" onclick="enviarAutoWhatsappCredenciales()">
                                    <i class="bi bi-send-fill me-1"></i> Envío Automático (n8n / API)
                                </button>
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
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label form-label-custom"><i class="bi bi-person"></i> Nombres</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" placeholder="Ej: Juan" value="{{ old('nombre') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label form-label-custom"><i class="bi bi-person"></i> Apellidos</label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control form-control-custom" placeholder="Ej: Pérez García" value="{{ old('apellidos') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="cedula" class="form-label form-label-custom"><i class="bi bi-card-text"></i> Identificación</label>
                                    <input type="text" name="cedula" id="cedula" class="form-control form-control-custom" placeholder="Formato nacional o pasaporte" value="{{ old('cedula') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label form-label-custom"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                                    <input type="email" name="email" id="email" class="form-control form-control-custom" placeholder="nombre@ejemplo.com" value="{{ old('email') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label form-label-custom"><i class="bi bi-whatsapp"></i> Teléfono / WhatsApp</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control form-control-custom" placeholder="Ej: 50688889999" value="{{ old('telefono') }}">
                                    <div class="form-text text-white-50 small">Incluya el código de país (ej: 506 para CR).</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="password" class="form-label form-label-custom mb-0"><i class="bi bi-shield-lock"></i> Contraseña</label>
                                        <button class="btn btn-secondary-custom py-0 px-2" style="font-size: 0.75rem;" type="button" id="generatePasswordBtn">
                                            <i class="bi bi-magic me-1"></i> Generar segura
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control form-control-custom border-end-0" placeholder="Escriba o genere una" required>
                                        <button class="btn btn-outline-secondary border-start-0 px-3 border-2" type="button" id="togglePasswordVisibility" style="border-radius: 0 0.75rem 0.75rem 0; border-color: var(--border-dark); background-color: rgba(255,255,255,0.02);">
                                            <i class="bi bi-eye-slash text-white-50"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top" style="border-color: var(--border-dark) !important;">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary-custom py-2 px-4">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-submit px-4 py-2">
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

            function generateRandomPassword(length = 8) {
                const charset = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
                let password = "";
                for (let i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                return password;
            }
        });

        function copiarTarjetaCompleta() {
            const texto = document.getElementById('wa_editable_card')?.value || '';
            if (!texto) return;
            navigator.clipboard.writeText(texto).then(() => {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tarjeta copiada al portapapeles', showConfirmButton: false, timer: 1800 });
            });
        }

        function abrirWhatsAppTarjeta() {
            const texto = encodeURIComponent(document.getElementById('wa_editable_card')?.value || '');
            let phone = (document.getElementById('wa_target_phone')?.value || '').replace(/\D+/g, '');
            if (phone.length === 8) phone = '506' + phone;
            const url = phone
                ? `https://api.whatsapp.com/send?phone=${phone}&text=${texto}`
                : `https://api.whatsapp.com/send?text=${texto}`;
            window.open(url, '_blank');
        }

        async function enviarCredencialesPorCorreo(email, nombre) {
            const mensaje = (document.getElementById('wa_editable_card')?.value || '').trim();
            if (!email) {
                Swal.fire('Correo requerido', 'El usuario no tiene un correo electrónico válido.', 'warning');
                return;
            }
            const btn = document.getElementById('btnEnviarEmailCreds');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';
            try {
                const res = await fetch("{{ route('usuarios.enviar_credenciales_email') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, nombre, mensaje })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire('¡Correo Enviado!', data.message || 'Credenciales enviadas por correo con éxito.', 'success');
                } else {
                    Swal.fire('Error SMTP', data.message || 'No se pudo enviar el correo. Verifica la configuración SMTP.', 'error');
                }
            } catch (e) {
                Swal.fire('Error de red', 'No se pudo conectar con el servidor.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }

        async function enviarAutoWhatsappCredenciales() {
            const telefono = (document.getElementById('wa_target_phone')?.value || '').trim();
            const mensaje = (document.getElementById('wa_editable_card')?.value || '').trim();
            if (!telefono) {
                Swal.fire('Teléfono requerido', 'Ingresa el número de WhatsApp del usuario para enviarlo automáticamente.', 'warning');
                return;
            }
            const btn = document.getElementById('btnAutoWaCreds');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';
            try {
                const res = await fetch("{{ route('usuarios.enviar_credenciales_wa') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ telefono, mensaje })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire('¡Enviado!', data.message || 'Credenciales enviadas por WhatsApp.', 'success');
                } else {
                    Swal.fire('Aviso', data.message || 'No se pudo enviar automáticamente. Usa "Enviar por WhatsApp".', 'warning');
                }
            } catch (e) {
                Swal.fire('Error de red', 'No se pudo conectar con el servidor.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    </script>
@endsection
