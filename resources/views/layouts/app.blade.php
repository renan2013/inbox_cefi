<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('cliente.nombre', 'CEFI') . ' - Inbox BPM')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        :root {
            --primary: #5fb230;
            --primary-dark: #4e9a26;
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --border-dark: #334155;
            --text-muted: #94a3b8;
            --text-light: #f8fafc;
        }

        /* Light theme overrides */
        [data-theme="light"] {
            --bg-dark: #f8fafc;
            --card-dark: #ffffff;
            --border-dark: #cbd5e1;
            --text-muted: #475569;
            --text-light: #0f172a;
        }

        [data-theme="light"] .text-white {
            color: #0f172a !important;
        }
        [data-theme="light"] .text-white-50 {
            color: #475569 !important;
        }
        [data-theme="light"] .text-light {
            color: #1e293b !important;
        }
        [data-theme="light"] .navbar-custom .text-light,
        [data-theme="light"] .brand-logo,
        [data-theme="light"] .navbar-custom .text-white-50 {
            color: #0f172a !important;
        }
        [data-theme="light"] .nav-link-custom {
            color: #475569 !important;
        }
        .nav-link-config {
            color: #6ee7b7 !important;
            font-weight: 700;
        }
        [data-theme="light"] .nav-link-config {
            color: #15803d !important;
            font-weight: 700;
        }
        [data-theme="light"] .nav-link-config:hover,
        [data-theme="light"] .nav-link-config.active {
            color: #166534 !important;
            background-color: rgba(22, 163, 74, 0.08) !important;
        }
        .btn-gear-config {
            color: #6ee7b7 !important;
        }
        [data-theme="light"] .btn-gear-config {
            color: #15803d !important;
            border-color: #cbd5e1 !important;
            background-color: #f8fafc !important;
        }
        [data-theme="light"] .btn-gear-config:hover {
            color: #166534 !important;
            background-color: #f1f5f9 !important;
        }
        [data-theme="light"] .nav-link-custom:hover, 
        [data-theme="light"] .nav-link-custom.active {
            color: #0f172a !important;
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
        [data-theme="light"] .dropdown-menu-dark-custom {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }
        [data-theme="light"] .dropdown-item-custom {
            color: #475569 !important;
        }
        [data-theme="light"] .dropdown-item-custom:hover {
            background-color: rgba(95, 178, 48, 0.1) !important;
            color: #0f172a !important;
        }
        [data-theme="light"] .accordion-custom .accordion-item,
        [data-theme="light"] .accordion-custom .accordion-button {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }
        [data-theme="light"] .accordion-custom .accordion-button::after {
            filter: none !important;
        }
        [data-theme="light"] .accordion-custom .accordion-button:not(.collapsed) {
            background-color: rgba(0, 0, 0, 0.02) !important;
        }
        [data-theme="light"] .card {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        [data-theme="light"] .table-custom td, [data-theme="light"] .table-custom th {
            color: #1e293b !important;
        }
        [data-theme="light"] .form-control-custom,
        [data-theme="light"] .form-select-custom,
        [data-theme="light"] .form-control,
        [data-theme="light"] .form-select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        [data-theme="light"] .form-control-custom:focus,
        [data-theme="light"] .form-select-custom:focus,
        [data-theme="light"] .form-control:focus,
        [data-theme="light"] .form-select:focus {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 178, 48, 0.15) !important;
        }
        [data-theme="light"] .form-label-custom,
        [data-theme="light"] .form-label {
            color: #475569 !important;
        }
        [data-theme="light"] .btn-outline-light {
            border-color: #475569 !important;
            color: #475569 !important;
        }
        [data-theme="light"] .btn-outline-light:hover {
            background-color: #475569 !important;
            color: #ffffff !important;
        }
        [data-theme="light"] .bg-dark,
        [data-theme="light"] .bg-dark-opacity-30,
        [data-theme="light"] .bg-dark-opacity-10 {
            background-color: #f1f5f9 !important;
        }
        [data-theme="light"] .table-dark {
            --bs-table-bg: transparent !important;
            --bs-table-color: #0f172a !important;
            --bs-table-border-color: #cbd5e1 !important;
            background: transparent !important;
            color: #0f172a !important;
        }
        [data-theme="light"] .page-header {
            background: var(--card-dark) !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        [data-theme="light"] .page-header h1,
        [data-theme="light"] .page-header h2,
        [data-theme="light"] .page-header h3 {
            color: #0f172a !important;
        }
        [data-theme="light"] [style*="color: #f8fafc"],
        [data-theme="light"] [style*="color:#f8fafc"],
        [data-theme="light"] [style*="color: #e2e8f0"],
        [data-theme="light"] [style*="color:#e2e8f0"],
        [data-theme="light"] [style*="color: #cbd5e1"],
        [data-theme="light"] [style*="color:#cbd5e1"],
        [data-theme="light"] [style*="color: #ffffff"],
        [data-theme="light"] [style*="color:#ffffff"],
        [data-theme="light"] [style*="color: white"],
        [data-theme="light"] [style*="color:white"] {
            color: #0f172a !important;
        }
        [data-theme="light"] .result-item {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        [data-theme="light"] .result-item:hover {
            background-color: var(--primary) !important;
            color: #ffffff !important;
        }
        [data-theme="light"] .btn-primary,
        [data-theme="light"] .btn-success,
        [data-theme="light"] .btn-danger,
        [data-theme="light"] .btn-info,
        [data-theme="light"] .btn-warning,
        [data-theme="light"] .btn-course-panel {
            color: #ffffff !important;
        }

        /* Dark mode text contrast adjustments */
        [data-theme="dark"] .text-muted,
        html:not([data-theme="light"]) .text-muted {
            color: #cbd5e1 !important;
        }

        [data-theme="dark"] .text-white-50,
        html:not([data-theme="light"]) .text-white-50 {
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .text-secondary,
        html:not([data-theme="light"]) .text-secondary {
            color: #cbd5e1 !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar-custom {
            background-color: var(--card-dark);
            border-bottom: 1px solid var(--border-dark);
            padding: 0.75rem 2rem;
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .brand-logo img {
            height: 22px;
            width: auto;
        }
        .brand-logo span {
            color: var(--primary);
            margin-right: 0.5rem;
        }

        .logo-dark-theme {
            display: block !important;
            height: 34px !important;
            width: auto !important;
        }
        .logo-light-theme {
            display: none !important;
            height: 34px !important;
            width: auto !important;
        }

        [data-theme="light"] .logo-dark-theme { display: none !important; }
        [data-theme="light"] .logo-light-theme { display: block !important; }

        /* Dropdown Styling */
        .dropdown-menu-dark-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-item-custom {
            color: var(--text-muted);
            border-radius: 0.5rem;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .dropdown-item-custom i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            color: var(--primary);
            transition: transform 0.2s ease;
        }

        .dropdown-item-custom:hover {
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--text-light);
        }

        .dropdown-item-custom:hover i {
            transform: scale(1.1);
        }

        .dropdown-divider-custom {
            border-top: 1px solid var(--border-dark);
            margin: 0.5rem 0;
        }

        .dropdown-header-custom {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0.5rem 1.2rem;
        }

        .nav-link-custom {
            color: var(--text-muted);
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .nav-link-custom:hover, .nav-link-custom.active {
            color: var(--text-light);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .btn-custom-logout {
            background-color: transparent;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: 0.4rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .form-select-custom, .form-select-dash, select.form-select {
            padding-right: 2.5rem !important;
            background-position: right 0.85rem center !important;
        }

        .btn-custom-logout:hover {
            background-color: #ef4444;
            color: #ffffff;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .footer-custom {
            background-color: var(--card-dark);
            border-top: 1px solid var(--border-dark);
            padding: 1.5rem 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* Diseño 100% Ancho Completo (Full Width) para todo Inbox CEFI */
        .container, 
        .container-sm, 
        .container-md, 
        .container-lg, 
        .container-xl, 
        .container-xxl {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: clamp(1rem, 2vw, 2.5rem) !important;
            padding-right: clamp(1rem, 2vw, 2.5rem) !important;
        }

        .container-fluid {
            width: 100% !important;
            padding-left: clamp(1rem, 2vw, 2.5rem) !important;
            padding-right: clamp(1rem, 2vw, 2.5rem) !important;
        }

        main.flex-grow-1 {
            width: 100% !important;
            max-width: 100% !important;
        }
    </style>
    @yield('styles')
</head>
<body>
    @php
        $_hubLicenseStatus = \App\Services\ModuleService::getLicenseStatus();
        $_hubLicenseNotice = \App\Services\ModuleService::getLicenseNotice();
    @endphp
    @if($_hubLicenseStatus === 'suspendido' && !Request::is('api/*') && !Request::is('logout'))
    <div style="position:fixed;inset:0;z-index:99999;background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;display:flex;align-items:center;justify-content:center;padding:2rem;font-family:system-ui,-apple-system,sans-serif;">
        <div style="max-width:540px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);border-radius:20px;padding:2.5rem;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,0.4);">
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,0.15);color:#f87171;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.25rem;">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <h3 style="font-weight:800;margin-bottom:0.75rem;">Servicio Temporalmente en Mantenimiento Administrativo</h3>
            <p style="color:#cbd5e1;font-size:0.95rem;line-height:1.6;margin-bottom:1.5rem;">
                {{ $_hubLicenseNotice !== '' ? $_hubLicenseNotice : 'La licencia de uso de esta plataforma requiere atención administrativa. Por favor comuníquese con el proveedor oficial del sistema (renangalvan.net).' }}
            </p>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light rounded-pill px-4">Cerrar Sesión</button>
            </form>
        </div>
    </div>
    @endif
    @if($_hubLicenseStatus === 'aviso_cobro')
    <div style="background:linear-gradient(90deg,#f59e0b,#d97706);color:#0f172a;font-weight:700;font-size:0.85rem;padding:0.45rem 1rem;text-align:center;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        {{ $_hubLicenseNotice !== '' ? $_hubLicenseNotice : 'Aviso Administrativo: Renovación o facturación de licencia pendiente. Contacte a soporte técnico (renangalvan.net).' }}
    </div>
    @endif

    <!-- Header / Navbar CEFI v2.1 -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="brand-logo d-flex align-items-center text-decoration-none" href="{{ (Auth::check() && (Auth::user()->id_rol == 3 || (method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))) ? route('estudiante.portal') : route('dashboard') }}">
                <img src="{{ asset('imgs/SVG/logo_blanco.svg') }}" alt="Inbox Logo" class="logo-dark-theme">
                <img src="{{ asset('imgs/SVG/logo_color.svg') }}" alt="Inbox Logo" class="logo-light-theme">
                <span class="badge rounded-pill ms-1 fw-bold px-2 py-0 text-white" style="background-color: var(--primary, #5fb230) !important; font-size: 0.68rem; line-height: 1.45;">2.0</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <!-- Acceso Exclusivo para Estudiantes / Alumnos -->
                    @if(Auth::check() && (Auth::user()->id_rol == 3 || (method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno())))
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom fw-bold {{ Route::is('estudiante.portal') ? 'active' : '' }}" href="{{ route('estudiante.portal') }}">
                            <i class="bi bi-mortarboard-fill me-1 text-success"></i> Mi Portal Estudiantil
                        </a>
                    </li>
                    @module('estudiante_tcu')
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('tcu.*') ? 'active' : '' }}" href="{{ route('tcu.index') }}">
                            <i class="bi bi-journal-text me-1"></i> Mi TCU
                        </a>
                    </li>
                    @endmodule
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="{{ route('estudiante.mi_expediente_pdf') }}" target="_blank">
                            <i class="bi bi-file-earmark-person me-1"></i> Mi Expediente (PDF)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="{{ route('estudiante.mi_record_pdf') }}" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Mi Récord (PDF)
                        </a>
                    </li>
                    @endif

                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @php
                        $_averiasPendientes = 0;
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('soporte')) {
                                $_averiasPendientes = \Illuminate\Support\Facades\DB::table('soporte')->whereIn('estado', ['reportado', 'pendiente'])->count();
                            } elseif (\Illuminate\Support\Facades\Schema::hasTable('soportes')) {
                                $_averiasPendientes = \Illuminate\Support\Facades\DB::table('soportes')->whereIn('estado', ['reportado', 'pendiente'])->count();
                            }
                        } catch (\Throwable $e) {}
                    @endphp
                    @if(\App\Services\ModuleService::isEnabled('dashboard') && \App\Services\ModuleService::isEnabled('item.dashboard_link'))
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ (Route::is('dashboard') || Route::is('tareas.*')) ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                    @endif
                    @endif

                    <!-- Logística Dropdown -->
                    @module('inventario')
                    @if(Auth::check() && Auth::user()->id_rol == 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam me-1"></i> Logística
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('item.inventario_control')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('inventario.index') }}"><i class="bi bi-clipboard-data"></i> Control de Inventario</a></li>
                            @endmodule
                            @module('item.inventario_operaciones')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('inventario.operaciones') }}"><i class="bi bi-box-arrow-right"></i> Operaciones de Bodega</a></li>
                            @endmodule
                        </ul>
                    </li>
                    @endif
                    @endmodule

                    <!-- Gestionar Curso Dropdown (Jamás visible para rol Alumno) -->
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @module('gestion_cursos')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestionar Curso
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                             @module('item.supervision_cursos')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('supervision.index') }}"><i class="bi bi-eye"></i> Supervisión de Cursos</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             @endmodule
                             @module('item.mis_cursos')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('mis_cursos.index') }}"><i class="bi bi-book"></i> Mis Cursos</a></li>
                             @endmodule
                             @module('item.cortina_curso')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('cortina.index') }}"><i class="bi bi-display-fill text-primary"></i> Cortina de Eventos</a></li>
                             @endmodule
                             @module('item.mis_recursos')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('recursos.index') }}"><i class="bi bi-collection"></i> Mis Recursos</a></li>
                             @endmodule
                             @module('item.recursos_compartidos')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('recursos.compartidos') }}"><i class="bi bi-share"></i> Recursos Compartidos</a></li>
                             @endmodule
                             @module('item.videotutoriales')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('videotutoriales.index') }}"><i class="bi bi-play-btn"></i> Videotutoriales</a></li>
                             @endmodule
                             <li><hr class="dropdown-divider-custom"></li>
                             @module('item.biblioteca_medios')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('biblioteca.index') }}"><i class="bi bi-images"></i> Biblioteca de Medios</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             @endmodule
                             @module('item.resultados_encuesta')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('encuestas.resultados') }}"><i class="bi bi-bar-chart-fill"></i> Resultados Encuestas</a></li>
                             @endmodule
                             @if(Auth::check() && Auth::user()->id_rol == 1)
                             @module('item.banco_preguntas')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('encuestas.preguntas') }}"><i class="bi bi-clipboard2-check"></i> Banco de Preguntas</a></li>
                             @endmodule
                             @endif
                        </ul>
                    </li>
                    @endmodule
                    @endif

                    <!-- Estudiante Dropdown (Para personal administrativo/docente) -->
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @module('estudiante_tcu')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Estudiante
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('item.portal_estudiante')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('estudiante.portal') }}"><i class="bi bi-mortarboard text-success me-2"></i> Portal de Estudiantes</a></li>
                            @endmodule
                            @module('item.bitacora_tcu')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('tcu.index') }}"><i class="bi bi-journal-text"></i> Bitácora de TCU</a></li>
                            @endmodule
                        </ul>
                    </li>
                    @endmodule
                    @endif

                    <!-- Soporte Dropdown (Personal Administrativo / Docente) -->
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @module('soporte')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Soporte
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                             @module('item.gestionar_soporte')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.gestionar') }}">Gestionar Soporte</a></li>
                             @endmodule
                             @module('item.categorias_soporte')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.categorias') }}">Gestionar Categorías</a></li>
                             @endmodule
                             @module('item.lista_soporte')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.lista') }}">Lista de Soporte</a></li>
                             @endmodule
                             <li><hr class="dropdown-divider-custom"></li>
                             @module('item.gestionar_claves')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('claves.index') }}">Gestionar Claves</a></li>
                             @endmodule
                             @module('inbox_ai')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.conocimiento_ai') }}"><i class="bi bi-cpu-fill text-success me-2"></i> Cerebro Inbox AI 2.0</a></li>
                             @endmodule
                             <li><hr class="dropdown-divider-custom"></li>
                             @module('item.centro_soporte')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('centro_soporte.index') }}">Centro de Soporte</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             @endmodule
                             @module('item.automatizaciones')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('plantillas.index') }}"><i class="bi bi-gear-wide-connected"></i> Automatizaciones</a></li>
                             @endmodule
                             @module('item.campanas_whatsapp')
                             <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('whatsapp.campanas.index') }}"><i class="bi bi-megaphone-fill text-warning me-2"></i> Difusión y Campañas WhatsApp</a></li>
                             @endmodule
                             @module('item.prospectos_marketing')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('marketing.prospectos.index') }}"><i class="bi bi-person-lines-fill text-primary me-2"></i> Base de Prospectos (Marketing)</a></li>
                             @endmodule
                        </ul>
                    </li>
                    @endmodule
                    @endif

                    <!-- Planilla Dropdown (Personal Administrativo / Docente) -->
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @module('planilla')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Planilla
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('item.planilla_marcar')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('asistencia.marcar') }}"><i class="bi bi-clock"></i> Registrar Asistencia</a></li>
                            @endmodule
                            @module('item.planilla_credencial')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.credencial') }}"><i class="bi bi-qr-code-scan"></i> Mi Credencial QR</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><h6 class="dropdown-header-custom">Administración</h6></li>
                            @module('item.planilla_panel')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.index') }}"><i class="bi bi-speedometer"></i> Panel de Planilla</a></li>
                            @endmodule
                            @module('item.planilla_escanear')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.escanear') }}"><i class="bi bi-camera"></i> Estación de Escaneo</a></li>
                            @endmodule
                            @module('item.planilla_empleados')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.empleados') }}"><i class="bi bi-person-gear"></i> Configurar Empleados</a></li>
                            @endmodule
                            @module('item.planilla_historial')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('asistencia.historial') }}"><i class="bi bi-calendar3"></i> Historial Asistencias</a></li>
                            @endmodule
                            @module('item.planilla_calculo')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.calcular') }}"><i class="bi bi-calculator"></i> Cálculo de Planilla</a></li>
                            @endmodule
                            @module('item.planilla_pagos')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.historial_pagos') }}"><i class="bi bi-receipt-cutoff"></i> Historial de Pagos</a></li>
                            @endmodule
                        </ul>
                    </li>
                    @endmodule
                    @endif

                    <!-- Registro Dropdown (Jamás visible para el rol Alumno) -->
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @if(\App\Services\ModuleService::isEnabled('registro_academico') || \App\Services\ModuleService::isEnabled('boletas_matricula') || \App\Services\ModuleService::isEnabled('finanzas') || \App\Services\ModuleService::isEnabled('expedientes_360'))
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Registro
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('boletas_matricula')
                            @module('item.generar_boleta')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.generar') }}"><i class="bi bi-receipt"></i> Generar Boleta de Pago</a></li>
                            @endmodule
                            @module('item.gestion_boletas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.index') }}"><i class="bi bi-clock-history"></i> Historial de Boletas</a></li>
                            @endmodule
                            @endmodule

                            @module('finanzas')
                            @module('item.estado_cuenta')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.estado_cuenta') }}"><i class="bi bi-wallet2"></i> Estado de Cuenta</a></li>
                            @endmodule
                            @module('item.morosidad')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.morosidad') }}"><i class="bi bi-exclamation-triangle"></i> Control de Morosidad</a></li>
                            @endmodule
                            @endmodule

                            @module('item.parametros_pagos')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.pagos') }}"><i class="bi bi-gear-fill"></i> Configuración de Pagos y Firmas</a></li>
                            @endmodule
                            @module('item.repositorio_actas')
                            <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('registro.repositorio_actas') }}"><i class="bi bi-journal-bookmark-fill text-danger"></i> Repositorio de Actas</a></li>
                            @endmodule

                            @module('registro_academico')
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.registrar_usuario')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('usuarios.create') }}"><i class="bi bi-person-plus"></i> Registrar Usuario</a></li>
                            @endmodule
                            @module('item.lista_usuarios')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('usuarios.index') }}"><i class="bi bi-people"></i> Lista de Usuarios</a></li>
                            @endmodule
                            @endmodule

                            @module('expedientes_360')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('expedientes.index') }}"><i class="bi bi-folder2-open"></i> Expedientes Digitales 360°</a></li>
                            @module('item.crear_expediente')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('expedientes.create') }}"><i class="bi bi-folder-plus"></i> Crear Expediente</a></li>
                            @endmodule
                            @endmodule

                            @module('registro_academico')
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.registrar_programa')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.create') }}"><i class="bi bi-mortarboard"></i> Registrar Programa</a></li>
                            @endmodule
                            @module('item.registrar_curso')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('cursos.create') }}"><i class="bi bi-book-half"></i> Registrar Curso</a></li>
                            @endmodule
                            @module('item.gestionar_grupos')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('grupos.index') }}"><i class="bi bi-grid-3x3-gap"></i> Gestionar Grupos</a></li>
                            @endmodule
                            @module('item.formularios_registro')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('solicitudes.index') }}"><i class="bi bi-ui-checks"></i> Formularios de Registro</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.lista_programas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.index') }}"><i class="bi bi-list-task"></i> Lista de Programas</a></li>
                            @endmodule
                            @module('item.lista_cursos')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('cursos.index') }}"><i class="bi bi-journals"></i> Lista de Cursos</a></li>
                            @endmodule
                            @module('item.programas_completos')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.completos') }}"><i class="bi bi-file-earmark-ruled"></i> Programas Completos</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><h6 class="dropdown-header-custom"><i class="bi bi-mortarboard-fill me-1"></i> Control Académico (Fase 4)</h6></li>
                            @module('item.convalidaciones')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('convalidaciones.index') }}"><i class="bi bi-patch-check"></i> Convalidaciones & Homologaciones</a></li>
                            @endmodule
                            @module('item.retiros')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('retiros.index') }}"><i class="bi bi-arrow-left-right"></i> Retiros y Cambios de Curso</a></li>
                            @endmodule
                            @module('item.graduaciones')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('graduaciones.index') }}"><i class="bi bi-award"></i> Auditoría y Graduaciones</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.etiquetas_tareas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('tareas.etiquetas.index') }}"><i class="bi bi-bookmarks text-warning"></i> Etiquetas de Tareas</a></li>
                            @endmodule
                            @module('item.plantillas_tareas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('tareas.plantillas.gestionar') }}"><i class="bi bi-card-checklist text-primary"></i> Plantillas de Tareas</a></li>
                            @endmodule
                            @if(\App\Services\ModuleService::isEnabled('item.saludos_plantillas') || \App\Services\ModuleService::isEnabled('item.saludos_categorias'))
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><h6 class="dropdown-header-custom"><i class="bi bi-chat-heart-fill text-success me-1"></i> Módulo Saludos</h6></li>
                            @module('item.saludos_plantillas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('saludos.plantillas') }}"><i class="bi bi-chat-heart"></i> Gestionar Plantillas de Saludo</a></li>
                            @endmodule
                            @module('item.saludos_categorias')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('saludos.categorias') }}"><i class="bi bi-folder2"></i> Categorías de Saludos</a></li>
                            @endmodule
                            @endif
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.reportes_sistema')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('reportes.index') }}"><i class="bi bi-file-earmark-pdf"></i> Reportes del Sistema</a></li>
                            @endmodule
                            @module('item.registro_utilitarios')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('utilitarios.index') }}"><i class="bi bi-tools"></i> Utilitarios</a></li>
                            @endmodule
                            @endmodule
                        </ul>
                    </li>
                    @endif
                    @endif

                    <!-- Base de Datos & Eventos con Sorteos en Vivo -->
                    @if(Auth::check() && Auth::user()->id_rol == 1 && \App\Services\ModuleService::isEnabled('base_datos'))
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle {{ Request::is('base-datos*') || Request::is('cortina*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gift-fill text-warning me-1"></i> Base de Datos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('item.bd_cortina')
                            <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('cortina.index') }}"><i class="bi bi-display-fill text-primary me-2"></i> Cortina Digital de Eventos</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            @endmodule
                            @module('item.bd_sorteos')
                            <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('base_datos.index') }}"><i class="bi bi-collection-play-fill text-success me-2"></i> Eventos & Sorteos en Vivo</a></li>
                            @endmodule
                            @module('item.bd_directorio')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('base_datos.index') }}#tabProspectos"><i class="bi bi-telephone-fill text-primary me-2"></i> Directorio de Contactos</a></li>
                            @endmodule
                            @module('item.bd_exportar')
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('base_datos.exportar') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i> Descargar Base Completa (CSV)</a></li>
                            @endmodule
                        </ul>
                    </li>
                    @endif

                    <!-- Configuración Dropdown (Solo Administrador General con Clave) -->
                    @if(Auth::check() && Auth::user()->id_rol == 1 && \App\Services\ModuleService::isEnabled('menu_configuracion'))
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom nav-link-config dropdown-toggle {{ Request::is('configuracion*') || Request::is('*conocimiento-ai*') || Request::is('automatizaciones*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Configuración
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                            <li><h6 class="dropdown-header-custom"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Administración General</h6></li>
                            @module('item.cfg_panel')
                            <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('configuracion.index') }}"><i class="bi bi-speedometer2 text-info me-2"></i> Panel de Configuración</a></li>
                            @endmodule
                            @module('item.cfg_pagos')
                            <li><a class="dropdown-item dropdown-item-custom fw-bold text-success" href="{{ route('configuracion.pagos') }}"><i class="bi bi-cash-coin text-success me-2"></i> Pagos, Morosidad e Intereses</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><h6 class="dropdown-header-custom"><i class="bi bi-robot text-success me-1"></i> Tutor AI</h6></li>
                            @module('item.cfg_rag')
                            <li>
                                <a class="dropdown-item dropdown-item-custom fw-semibold {{ Request::is('*conocimiento-ai*') ? 'active' : '' }}" href="{{ route('configuracion.conocimiento_ai') }}">
                                    <i class="bi bi-mortarboard-fill text-info me-2"></i> Base de Conocimiento (RAG)
                                </a>
                            </li>
                            @endmodule
                            @module('item.cfg_chat_ai')
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)" onclick="const f = document.getElementById('inboxAiFab'); if(f) f.click();">
                                    <i class="bi bi-chat-dots-fill text-success me-2"></i> Abrir Chat Tutor AI
                                </a>
                            </li>
                            @endmodule
                            @module('item.cfg_automatizaciones')
                            <li><hr class="dropdown-divider-custom"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom fw-semibold {{ Request::is('automatizaciones*') ? 'active' : '' }}" href="{{ route('plantillas.index') }}">
                                    <i class="bi bi-gear-wide-connected text-success me-2"></i> Automatizaciones
                                </a>
                            </li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            @module('item.cfg_parametros')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'parametros']) }}"><i class="bi bi-buildings text-primary me-2"></i> Parámetros del Sistema</a></li>
                            @endmodule
                            @module('item.cfg_identidad')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'identidad']) }}"><i class="bi bi-palette text-warning me-2"></i> Logotipo e Identidad</a></li>
                            @endmodule
                            @module('item.cfg_n8n')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'n8n']) }}"><i class="bi bi-diagram-3-fill text-success me-2"></i> Parámetros n8n & WhatsApp</a></li>
                            @endmodule
                            @module('item.cfg_smtp')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'smtp']) }}"><i class="bi bi-envelope-at-fill text-warning me-2"></i> Correo Saliente (SMTP)</a></li>
                            @endmodule
                            @module('item.cfg_seguridad')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'seguridad']) }}"><i class="bi bi-key-fill text-info me-2"></i> Clave Maestra Superior</a></li>
                            @endmodule
                            <li><hr class="dropdown-divider-custom"></li>
                            <li>
                                <form action="{{ route('configuracion.salir') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item dropdown-item-custom text-danger">
                                        <i class="bi bi-lock-fill me-2"></i> Bloquear Acceso Seguro
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>

                <!-- User Profile & Logout -->
                <div class="d-flex align-items-center">
                    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
                    @if(\App\Services\ModuleService::isEnabled('dashboard') && \App\Services\ModuleService::isEnabled('item.averias_alerta'))
                    <button type="button"
                            class="btn btn-outline-warning rounded-circle me-2 position-relative"
                            style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-color: rgba(245, 158, 11, 0.45); color: #f59e0b;"
                            onclick="abrirModalReporteAveria()"
                            title="Reportar Avería o Problema del Sistema (Alerta Directa a Soporte)">
                        <i class="bi bi-exclamation-triangle-fill {{ ($_averiasPendientes ?? 0) > 0 ? 'text-warning' : '' }}"></i>
                        @if(($_averiasPendientes ?? 0) > 0)
                            <span id="navbar-averias-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark" style="font-size: 0.62rem; padding: 0.22em 0.45em;">
                                {{ $_averiasPendientes }}
                            </span>
                        @else
                            <span id="navbar-averias-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark d-none" style="font-size: 0.62rem; padding: 0.22em 0.45em;">0</span>
                        @endif
                    </button>
                    @endif
                    @endif
                    @if(Auth::check() && Auth::user()->id_rol == 1)
                    <a href="{{ route('configuracion.index') }}" class="btn btn-outline-secondary btn-gear-config rounded-circle me-2" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-color: var(--border-dark);" title="Configuración de la Plataforma">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    @endif
                    <button type="button" class="btn btn-outline-secondary rounded-circle me-3" id="theme-toggle" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-color: var(--border-dark); color: var(--text-muted);" onclick="toggleTheme()" title="Cambiar Tema">
                        <i id="theme-icon" class="bi bi-sun-fill"></i>
                    </button>
                    <span class="me-3 text-white-50">Hola, <strong>{{ Auth::user()->nombre }}</strong></span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-custom-logout">
                            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container text-center">
            <div class="mb-2">
                <img src="{{ asset('imgs/SVG/logo_blanco.svg') }}" alt="Inbox Logo" class="logo-dark-theme mx-auto" style="height: 28px;">
                <img src="{{ asset('imgs/SVG/logo_color.svg') }}" alt="Inbox Logo" class="logo-light-theme mx-auto" style="height: 28px;">
            </div>
            <p class="mb-0">&copy; {{ date('Y') }} BPM Intelligence System. All rights reserved.</p>
            <p class="mb-0 mt-1" style="font-size: 0.75rem; opacity: 0.75;">
                Designed and developed by <a href="https://renangalvan.net" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 600;">renangalvan.net</a>
            </p>
        </div>
    </footer>

    <!-- Modal Global Omnicanal: Reportar Avería del Sistema con Evidencia Gráfica -->
    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
    <div class="modal fade" id="modalReportarAveriaGlobal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--card-dark); color: var(--text-light); border: 1px solid var(--border-dark) !important;">
                <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, rgba(220,38,38,0.18), rgba(245,158,11,0.12)); border-color: var(--border-dark) !important;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-broadcast me-1"></i>Alerta Omnicanal</span>
                            <span class="small text-muted">Notifica a Dashboard + Hub Central + WhatsApp (n8n) + Correo</span>
                        </div>
                        <h5 class="modal-title fw-bold mb-0 mt-1">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Reportar Avería o Incidencia del Sistema
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('soporte.gestionar') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold" title="Ir al Tablero de Averías">
                            <i class="bi bi-list-check me-1"></i>Ver Averías ({{ $_averiasPendientes ?? 0 }})
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <form id="formReporteAveriaGlobal" onsubmit="enviarReporteAveriaGlobal(event)" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="url_origen" id="averia_url_origen">
                    <input type="hidden" name="evidencia_base64" id="averia_evidencia_base64">
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">
                                    <i class="bi bi-cursor-text text-warning me-1"></i>Asunto / Resumen del Problema *
                                </label>
                                <input type="text" name="titulo" id="averia_titulo" class="form-control" required placeholder="Ej: No permite descargar la boleta en PDF o error al guardar acta..." style="background-color: var(--bg-dark); color: var(--text-light); border-color: var(--border-dark);">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">
                                    <i class="bi bi-tags-fill text-info me-1"></i>Etiqueta / Categoría de la Causa *
                                </label>
                                <select name="etiqueta" id="averia_etiqueta" class="form-select" style="background-color: var(--bg-dark); color: var(--text-light); border-color: var(--border-dark);">
                                    <option value="🟠 Fallo en Módulo (Matrícula, Pagos, Cursos, Actas)">🟠 Fallo en Módulo (Matrícula, Pagos, Cursos, Actas)</option>
                                    <option value="🔴 Error Crítico / Bloqueo del Sistema">🔴 Error Crítico / Bloqueo del Sistema</option>
                                    <option value="🟡 Error Visual / Diseño / Formato PDF">🟡 Error Visual / Diseño / Formato PDF</option>
                                    <option value="🟢 Base de Datos / Sincronización">🟢 Base de Datos / Sincronización</option>
                                    <option value="🔵 Duda Operativa / Capacitación">🔵 Duda Operativa / Capacitación</option>
                                    <option value="🟣 Solicitud de Mejora">🟣 Solicitud de Mejora</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">
                                    <i class="bi bi-lightning-charge-fill text-danger me-1"></i>Nivel de Urgencia *
                                </label>
                                <select name="prioridad" id="averia_prioridad" class="form-select" style="background-color: var(--bg-dark); color: var(--text-light); border-color: var(--border-dark);">
                                    <option value="urgente">🚨 Urgente (Bloquea operación)</option>
                                    <option value="alta" selected>🔴 Alta (Requiere atención pronta)</option>
                                    <option value="media">🟠 Media (Fallo parcial)</option>
                                    <option value="baja">🔵 Baja (Consulta / Mejora)</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">
                                    <i class="bi bi-chat-left-text-fill text-primary me-1"></i>Causa / Descripción Detallada del Problema *
                                </label>
                                <textarea name="problema" id="averia_problema" rows="3" class="form-control" required placeholder="Describa qué acción estaba realizando, qué mensaje apareció o cuál es la causa..." style="background-color: var(--bg-dark); color: var(--text-light); border-color: var(--border-dark);"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1 d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-camera-fill text-success me-1"></i>Evidencia Gráfica (Captura de Pantalla)</span>
                                    <span class="badge bg-info bg-opacity-10 text-info fw-normal">Puedes subir archivo o presionar Ctrl + V para pegar captura</span>
                                </label>
                                <div id="averia_dropzone" class="p-3 rounded-3 text-center position-relative" style="border: 2px dashed rgba(56, 189, 248, 0.45); background: rgba(15, 23, 42, 0.25); cursor: pointer;" onclick="document.getElementById('averia_file_input').click()">
                                    <input type="file" name="evidencia_grafica" id="averia_file_input" accept="image/*" class="d-none" onchange="previsualizarEvidenciaArchivo(this)">
                                    <div id="averia_dropzone_placeholder">
                                        <i class="bi bi-cloud-arrow-up-fill fs-3 text-info d-block mb-1"></i>
                                        <div class="small fw-semibold">Haz clic para seleccionar una imagen o pega tu captura aquí con <kbd>Ctrl + V</kbd></div>
                                        <div class="text-muted" style="font-size: 0.74rem;">Formatos soportados: PNG, JPG, WEBP (Máx. 8 MB)</div>
                                    </div>
                                    <div id="averia_preview_wrapper" class="d-none">
                                        <img id="averia_preview_img" src="" alt="Vista previa de Evidencia Gráfica" style="max-height: 170px; max-width: 100%; border-radius: 8px; border: 1px solid rgba(56,189,248,0.5);">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="limpiarEvidenciaAveria(event)">
                                                <i class="bi bi-trash me-1"></i>Quitar captura
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2 d-flex align-items-center gap-1">
                                    <i class="bi bi-link-45deg text-info"></i>
                                    <span>Pantalla detectada automáticamente:</span>
                                    <code id="averia_url_label" class="text-info"></code>
                                </div>
                            </div>
                        </div>

                        @php
                            $_ultimasAveriasModal = collect();
                            try {
                                if (\Illuminate\Support\Facades\Schema::hasTable('soporte')) {
                                    $_ultimasAveriasModal = \Illuminate\Support\Facades\DB::table('soporte')
                                        ->orderByDesc('id')
                                        ->limit(8)
                                        ->get();
                                }
                            } catch (\Throwable $e) {}
                        @endphp
                        @if($_ultimasAveriasModal->count() > 0)
                        <div class="mt-4 pt-3 border-top" style="border-color: var(--border-dark) !important;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                <span class="small fw-bold text-uppercase text-warning">
                                    <i class="bi bi-clock-history me-1"></i>Reportes Activos / Recientes ({{ $_ultimasAveriasModal->count() }})
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0" style="font-size: 0.75rem;" onclick="eliminarTodasAveriasRapido()">
                                        <i class="bi bi-trash3-fill me-1"></i>Borrar Todos (Pruebas)
                                    </button>
                                </div>
                            </div>
                            <div class="list-group list-group-flush rounded-3 overflow-hidden border" style="border-color: var(--border-dark) !important; max-height: 185px; overflow-y: auto;" id="listaAveriasModalGlobal">
                                @foreach($_ultimasAveriasModal as $_avItem)
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" id="averia-modal-row-{{ $_avItem->id }}" style="background-color: var(--bg-dark); color: var(--text-light); border-color: var(--border-dark);">
                                    <div class="text-truncate me-2" style="max-width: 72%;">
                                        <span class="badge {{ $_avItem->estado === 'solucionado' ? 'bg-success' : 'bg-danger' }} rounded-pill me-1" style="font-size: 0.65rem;">
                                            #{{ $_avItem->id }} {{ strtoupper($_avItem->estado ?? 'reportado') }}
                                        </span>
                                        <span class="fw-semibold small">{{ $_avItem->titulo }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        @if(!empty($_avItem->adjunto))
                                            <a href="{{ asset('uploads/soporte/' . $_avItem->adjunto) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2" style="font-size: 0.72rem;" title="Ver Evidencia">
                                                <i class="bi bi-image"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="eliminarAveriaRapido({{ $_avItem->id }})" title="Borrar este reporte">
                                            <i class="bi bi-trash3 me-1"></i>Borrar
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between" style="border-color: var(--border-dark) !important;">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnEnviarAveriaGlobal" class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm text-dark">
                            <i class="bi bi-send-exclamation-fill me-1"></i>Enviar Avería (WhatsApp + Hub + Correo)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function eliminarAveriaRapido(id) {
            Swal.fire({
                title: '¿Borrar reporte #' + id + '?',
                text: 'Se eliminará este reporte de avería (útil para reportes de prueba o falsos) tanto aquí como en el Hub Central.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, borrar reporte',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;
                fetch("{{ url('/soporte') }}/" + id + "/eliminar", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const row = document.getElementById('averia-modal-row-' + id);
                        if (row) row.remove();
                        const badge = document.getElementById('navbar-averias-badge');
                        if (badge) {
                            const nextVal = Math.max(0, parseInt(badge.textContent || '1', 10) - 1);
                            badge.textContent = nextVal;
                            if (nextVal === 0) badge.classList.add('d-none');
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Reporte eliminado',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    }
                });
            });
        }

        function eliminarTodasAveriasRapido() {
            Swal.fire({
                title: '¿Borrar TODOS los reportes?',
                text: 'Se eliminarán todos los reportes de avería de prueba tanto en la plataforma como en Inbox Cloud Hub.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, borrar todos',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;
                fetch("{{ route('soporte.delete_all') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reportes eliminados',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    }
                });
            });
        }
        function abrirModalReporteAveria() {
            const urlActual = window.location.href;
            document.getElementById('averia_url_origen').value = urlActual;
            document.getElementById('averia_url_label').textContent = urlActual;
            const modalEl = document.getElementById('modalReportarAveriaGlobal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        function previsualizarEvidenciaArchivo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('averia_evidencia_base64').value = '';
                    document.getElementById('averia_preview_img').src = e.target.result;
                    document.getElementById('averia_dropzone_placeholder').classList.add('d-none');
                    document.getElementById('averia_preview_wrapper').classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function limpiarEvidenciaAveria(e) {
            if (e) e.stopPropagation();
            document.getElementById('averia_file_input').value = '';
            document.getElementById('averia_evidencia_base64').value = '';
            document.getElementById('averia_preview_img').src = '';
            document.getElementById('averia_preview_wrapper').classList.add('d-none');
            document.getElementById('averia_dropzone_placeholder').classList.remove('d-none');
        }

        // Permitir pegar captura de pantalla directamente con Ctrl + V cuando el modal está abierto
        document.addEventListener('paste', function(e) {
            const modalEl = document.getElementById('modalReportarAveriaGlobal');
            if (!modalEl || !modalEl.classList.contains('show')) return;
            const items = (e.clipboardData || e.originalEvent.clipboardData)?.items || [];
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        document.getElementById('averia_file_input').value = '';
                        document.getElementById('averia_evidencia_base64').value = ev.target.result;
                        document.getElementById('averia_preview_img').src = ev.target.result;
                        document.getElementById('averia_dropzone_placeholder').classList.add('d-none');
                        document.getElementById('averia_preview_wrapper').classList.remove('d-none');
                    };
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });

        function enviarReporteAveriaGlobal(e) {
            e.preventDefault();
            const form = document.getElementById('formReporteAveriaGlobal');
            const btn  = document.getElementById('btnEnviarAveriaGlobal');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando alerta...';

            fetch("{{ route('soporte.reportar_rapido') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-exclamation-fill me-1"></i>Enviar Avería (WhatsApp + Hub + Correo)';
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalReportarAveriaGlobal')).hide();
                    form.reset();
                    limpiarEvidenciaAveria();
                    const badge = document.getElementById('navbar-averias-badge');
                    if (badge) {
                        const current = parseInt(badge.textContent || '0', 10) + 1;
                        badge.textContent = current;
                        badge.classList.remove('d-none');
                    }
                    Swal.fire({
                        icon: 'success',
                        title: '¡Avería #' + res.ticket_id + ' Reportada!',
                        text: res.message,
                        confirmButtonColor: '#5fb230'
                    });
                } else {
                    Swal.fire('Atención', res.message || 'No se pudo registrar el reporte.', 'warning');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-exclamation-fill me-1"></i>Enviar Avería (WhatsApp + Hub + Correo)';
                Swal.fire('Error', 'Error de red al enviar el reporte de avería.', 'error');
            });
        }
    </script>
    @endif

    <!-- Widget Flotante Inbox AI 2.0 (Solo Administración y Docentes) -->
    @if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
    @include('partials.inbox_ai')
    @endif

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 Global -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                if (theme === 'light') {
                    icon.className = 'bi bi-moon-stars-fill';
                } else {
                    icon.className = 'bi bi-sun-fill';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const theme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeIcon(theme);

            // Ocultar automáticamente solo ítems auto-detectados cuyo nombre no esté habilitado en ningún otro menú activo
            @php
                $_enabledAutoLabels = [];
                $_disabledAutoLabels = [];
                foreach (\App\Services\ModuleService::discoverNavbarTree() as $_mKey => $_mMeta) {
                    $_pOn = \App\Services\ModuleService::isEnabled($_mKey);
                    foreach ($_mMeta['children'] ?? [] as $_cKey => $_cMeta) {
                        if (!empty($_cMeta['nombre'])) {
                            $_lbl = mb_strtolower(trim($_cMeta['nombre']), 'UTF-8');
                            if ($_pOn && \App\Services\ModuleService::isEnabled($_cKey)) {
                                $_enabledAutoLabels[] = $_lbl;
                            } else {
                                $_disabledAutoLabels[] = $_lbl;
                            }
                        }
                    }
                }
                $_disabledAutoLabels = array_values(array_diff(array_unique($_disabledAutoLabels), array_unique($_enabledAutoLabels)));
            @endphp
            const disabledLabels = @json($_disabledAutoLabels);
            if (disabledLabels.length > 0) {
                document.querySelectorAll('.navbar .dropdown-menu a.dropdown-item').forEach(a => {
                    const txt = (a.innerText || '').trim().replace(/\s+/g, ' ').toLowerCase();
                    if (disabledLabels.includes(txt)) {
                        const li = a.closest('li');
                        if (li) li.remove();
                    }
                });
            }

            // Limpiar divisores duplicados o extremos cuando se desactivan sub-ítems desde Inbox Cloud Hub
            document.querySelectorAll('.navbar .dropdown-menu').forEach(menu => {
                const items = Array.from(menu.children);
                let prevWasDivider = true;
                items.forEach(li => {
                    const isDivider = li.querySelector('hr.dropdown-divider-custom') !== null;
                    if (isDivider) {
                        if (prevWasDivider) {
                            li.style.display = 'none';
                        }
                        prevWasDivider = true;
                    } else if (li.querySelector('.dropdown-header-custom') === null) {
                        prevWasDivider = false;
                    }
                });
                const visibleItems = Array.from(menu.children).filter(li => li.style.display !== 'none');
                if (visibleItems.length > 0) {
                    const lastLi = visibleItems[visibleItems.length - 1];
                    if (lastLi.querySelector('hr.dropdown-divider-custom')) {
                        lastLi.style.display = 'none';
                    }
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
