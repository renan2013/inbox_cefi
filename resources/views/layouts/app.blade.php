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
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header / Navbar CEFI v2.1 -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="brand-logo d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
                <img src="{{ asset('imgs/SVG/logo_blanco.svg') }}" alt="Inbox Logo" class="logo-dark-theme">
                <img src="{{ asset('imgs/SVG/logo_color.svg') }}" alt="Inbox Logo" class="logo-light-theme">
                <span class="badge rounded-pill ms-2 fs-6 fw-bold px-2 py-1 text-white" style="background-color: var(--primary, #5fb230) !important;">2.0</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <!-- Dashboard -->
                    @module('dashboard')
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    @endmodule

                    <!-- Logística Dropdown -->
                    @module('inventario')
                    @if(Auth::check() && Auth::user()->id_rol == 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam me-1"></i> Logística
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('inventario.index') }}"><i class="bi bi-clipboard-data"></i> Control de Inventario</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('inventario.operaciones') }}"><i class="bi bi-box-arrow-right"></i> Operaciones de Bodega</a></li>
                        </ul>
                    </li>
                    @endif
                    @endmodule

                    <!-- Gestionar Curso Dropdown -->
                    @module('gestion_cursos')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestionar Curso
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('supervision.index') }}"><i class="bi bi-eye"></i> Supervisión de Cursos</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('mis_cursos.index') }}"><i class="bi bi-book"></i> Mis Cursos</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('recursos.index') }}"><i class="bi bi-collection"></i> Mis Recursos</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('recursos.compartidos') }}"><i class="bi bi-share"></i> Recursos Compartidos</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('videotutoriales.index') }}"><i class="bi bi-play-btn"></i> Videotutoriales</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('biblioteca.index') }}"><i class="bi bi-images"></i> Biblioteca de Medios</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('encuestas.resultados') }}"><i class="bi bi-bar-chart-fill"></i> Resultados Encuestas</a></li>
                             @if(Auth::check() && Auth::user()->id_rol == 1)
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('encuestas.preguntas') }}"><i class="bi bi-clipboard2-check"></i> Banco de Preguntas</a></li>
                             @endif
                        </ul>
                    </li>
                    @endmodule

                    <!-- Estudiante Dropdown -->
                    @module('estudiante_tcu')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Estudiante
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('tcu.index') }}"><i class="bi bi-journal-text"></i> Bitácora de TCU</a></li>
                        </ul>
                    </li>
                    @endmodule

                    <!-- Soporte Dropdown -->
                    @module('soporte')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Soporte
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.gestionar') }}">Gestionar Soporte</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.categorias') }}">Gestionar Categorías</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('soporte.lista') }}">Lista de Soporte</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('claves.index') }}">Gestionar Claves</a></li>
                             @module('inbox_ai')
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.conocimiento_ai') }}"><i class="bi bi-cpu-fill text-success me-2"></i> Cerebro Inbox AI 2.0</a></li>
                             @endmodule
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('centro_soporte.index') }}">Centro de Soporte</a></li>
                             <li><hr class="dropdown-divider-custom"></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('plantillas.index') }}"><i class="bi bi-gear-wide-connected"></i> Automatizaciones</a></li>
                             @module('whatsapp_n8n')
                             <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('whatsapp.campanas.index') }}"><i class="bi bi-megaphone-fill text-warning me-2"></i> Difusión y Campañas WhatsApp</a></li>
                             <li><a class="dropdown-item dropdown-item-custom" href="{{ route('marketing.prospectos.index') }}"><i class="bi bi-person-lines-fill text-primary me-2"></i> Base de Prospectos (Marketing)</a></li>
                             @endmodule
                        </ul>
                    </li>
                    @endmodule

                    <!-- Planilla Dropdown -->
                    @module('planilla')
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Planilla
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('asistencia.marcar') }}"><i class="bi bi-clock"></i> Registrar Asistencia</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.credencial') }}"><i class="bi bi-qr-code-scan"></i> Mi Credencial QR</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><h6 class="dropdown-header-custom">Administración</h6></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.index') }}"><i class="bi bi-speedometer"></i> Panel de Planilla</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.escanear') }}"><i class="bi bi-camera"></i> Estación de Escaneo</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.empleados') }}"><i class="bi bi-person-gear"></i> Configurar Empleados</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('asistencia.historial') }}"><i class="bi bi-calendar3"></i> Historial Asistencias</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.calcular') }}"><i class="bi bi-calculator"></i> Cálculo de Planilla</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('planilla.historial_pagos') }}"><i class="bi bi-receipt-cutoff"></i> Historial de Pagos</a></li>
                        </ul>
                    </li>
                    @endmodule

                    <!-- Registro Dropdown -->
                    @if(\App\Services\ModuleService::isEnabled('registro_academico') || \App\Services\ModuleService::isEnabled('boletas_matricula') || \App\Services\ModuleService::isEnabled('finanzas') || \App\Services\ModuleService::isEnabled('expedientes_360'))
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Registro
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom">
                            @module('boletas_matricula')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.generar') }}"><i class="bi bi-receipt"></i> Generar Boleta de Pago</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.index') }}"><i class="bi bi-clock-history"></i> Historial de Boletas</a></li>
                            @endmodule

                            @module('finanzas')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.estado_cuenta') }}"><i class="bi bi-wallet2"></i> Estado de Cuenta</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('boletas.morosidad') }}"><i class="bi bi-exclamation-triangle"></i> Control de Morosidad</a></li>
                            @endmodule

                            @module('registro_academico')
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('usuarios.create') }}"><i class="bi bi-person-plus"></i> Registrar Usuario</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('usuarios.index') }}"><i class="bi bi-people"></i> Lista de Usuarios</a></li>
                            @endmodule

                            @module('expedientes_360')
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('expedientes.index') }}"><i class="bi bi-folder2-open"></i> Expedientes Digitales 360°</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('expedientes.create') }}"><i class="bi bi-folder-plus"></i> Crear Expediente</a></li>
                            @endmodule

                            @module('registro_academico')
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.create') }}"><i class="bi bi-mortarboard"></i> Registrar Programa</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('cursos.create') }}"><i class="bi bi-book-half"></i> Registrar Curso</a></li>                        
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('grupos.index') }}"><i class="bi bi-grid-3x3-gap"></i> Gestionar Grupos</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.index') }}"><i class="bi bi-list-task"></i> Lista de Programas</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('cursos.index') }}"><i class="bi bi-journals"></i> Lista de Cursos</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('programas.completos') }}"><i class="bi bi-file-earmark-ruled"></i> Programas Completos</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('reportes.index') }}"><i class="bi bi-file-earmark-pdf"></i> Reportes del Sistema</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('utilitarios.index') }}"><i class="bi bi-tools"></i> Utilitarios</a></li>
                            @endmodule
                        </ul>
                    </li>
                    @endif

                    <!-- Configuración Dropdown (Solo Administrador General con Clave) -->
                    @if(Auth::check() && Auth::user()->id_rol == 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom nav-link-config dropdown-toggle {{ Request::is('configuracion*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-sliders2-vertical me-1"></i> Configuración
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                            <li><h6 class="dropdown-header-custom"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Administración General</h6></li>
                            <li><a class="dropdown-item dropdown-item-custom fw-bold" href="{{ route('configuracion.index') }}"><i class="bi bi-speedometer2 text-info me-2"></i> Panel de Configuración</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'parametros']) }}"><i class="bi bi-buildings text-primary me-2"></i> Parámetros del Sistema</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'identidad']) }}"><i class="bi bi-palette text-warning me-2"></i> Logotipo e Identidad</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'n8n']) }}"><i class="bi bi-diagram-3-fill text-success me-2"></i> Parámetros n8n & WhatsApp</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'modulos']) }}"><i class="bi bi-toggles2 text-danger me-2"></i> Módulos y Personalización</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="{{ route('configuracion.index', ['tab' => 'seguridad']) }}"><i class="bi bi-key-fill text-info me-2"></i> Clave Maestra Superior</a></li>
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

    <!-- Widget Flotante Inbox AI 2.0 -->
    @include('partials.inbox_ai')

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
        });
    </script>
    @yield('scripts')
</body>
</html>
