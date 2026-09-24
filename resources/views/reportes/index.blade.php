@extends('layouts.app')

@section('title', 'Inbox BPM - Generador de Reportes')

@section('styles')
    <style>
        .page-header {
            background: var(--card-dark, #ffffff);
            padding: 2rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04);
            margin-bottom: 2rem;
            border: 1px solid var(--border-dark, #e2e8f0);
        }

        .section-title {
            font-weight: 800;
            color: var(--text-dark, #0f172a);
            letter-spacing: -0.025em;
            font-size: 2rem;
        }

        .glass-card {
            background: var(--card-dark, #ffffff);
            border-radius: 1.5rem;
            border: 1px solid var(--border-dark, #e2e8f0);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.08);
            border-color: var(--primary, #5fb230);
        }

        .card-icon {
            font-size: 2.75rem;
            color: var(--primary, #5fb230);
            opacity: 0.25;
            position: absolute;
            top: 20px;
            right: 20px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .glass-card:hover .card-icon {
            opacity: 0.5;
            transform: scale(1.1) rotate(5deg);
        }

        /* Botones Ovalados Limpios */
        .btn-report-pill {
            display: flex;
            align-items: center;
            background-color: var(--card-dark, #ffffff);
            border: 1.5px solid var(--border-dark, #cbd5e1);
            color: var(--text-light, #1e293b);
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.75rem 1.25rem;
            border-radius: 50rem;
            transition: all 0.25s ease;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .btn-report-pill:hover {
            background-color: rgba(95, 178, 48, 0.1);
            border-color: var(--primary, #5fb230);
            color: var(--primary, #5fb230);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(95, 178, 48, 0.15);
        }

        .btn-report-pill i {
            margin-right: 10px;
            font-size: 1.15rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50 small fw-semibold"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white small fw-bold" aria-current="page">Generador de Reportes</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="section-title mb-1">Generador de Reportes</h1>
            <p class="text-white-50 mb-0">Exporta y consulte informes institucionales detallados en formato PDF y hojas de cálculo.</p>
        </div>

        <div class="row g-4">
            <!-- Tareas -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 p-4">
                    <i class="bi bi-list-check card-icon"></i>
                    <h5 class="fw-bold mb-1 text-white fs-5">Tareas y Proyectos</h5>
                    <p class="text-white-50 small mb-4">Informes sobre el estado y asignación de tareas del personal.</p>
                    <div class="d-grid gap-3">
                        <a href="{{ route('reportes.generar', ['tipo' => 'tareas_todas']) }}" class="btn-report-pill">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Listado de Tareas
                        </a>
                        <a href="{{ route('reportes.generar', ['tipo' => 'tareas_pendientes']) }}" class="btn-report-pill">
                            <i class="bi bi-clock-history text-warning"></i> Tareas Pendientes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usuarios -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 p-4">
                    <i class="bi bi-people card-icon"></i>
                    <h5 class="fw-bold mb-1 text-white fs-5">Usuarios del Sistema</h5>
                    <p class="text-white-50 small mb-4">Directorio del personal, docentes y estudiantes registrados.</p>
                    <div class="d-grid gap-3">
                        <a href="{{ route('reportes.generar', ['tipo' => 'usuarios_lista']) }}" class="btn-report-pill">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Padrón de Usuarios (PDF)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Credenciales -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 p-4">
                    <i class="bi bi-shield-lock card-icon"></i>
                    <h5 class="fw-bold mb-1 text-white fs-5">Credenciales Institucionales</h5>
                    <p class="text-white-50 small mb-4">Historial de accesos, contraseñas y plataformas gestionadas.</p>
                    <div class="d-grid gap-3">
                        <a href="{{ route('reportes.generar', ['tipo' => 'credenciales_lista']) }}" class="btn-report-pill">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Reporte de Accesos (PDF)
                        </a>
                        <a href="{{ route('reportes.ingresos_drive') }}" class="btn-report-pill" style="border-color: rgba(95, 178, 48, 0.4);">
                            <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i> <span class="text-success">Ingresos en Tiempo Real (Drive)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Académicos -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 p-4">
                    <i class="bi bi-mortarboard card-icon"></i>
                    <h5 class="fw-bold mb-1 text-white fs-5">Oferta Académica</h5>
                    <p class="text-white-50 small mb-4">Estructura de programas, mallas y estado de supervisión.</p>
                    <div class="d-grid gap-3">
                        <a href="{{ route('reportes.generar', ['tipo' => 'programas_lista']) }}" class="btn-report-pill">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Lista de Programas
                        </a>
                        <a href="{{ route('reportes.generar', ['tipo' => 'grupos_activos']) }}" class="btn-report-pill">
                            <i class="bi bi-journal-check text-success"></i> Cursos Activos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Finanzas -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 p-4">
                    <i class="bi bi-cash-coin card-icon"></i>
                    <h5 class="fw-bold mb-1 text-white fs-5">Finanzas y Cartera</h5>
                    <p class="text-white-50 small mb-4">Control de cuotas pendientes, mora e ingresos acumulados.</p>
                    <div class="d-grid gap-3">
                        <a href="{{ route('boletas.morosidad') }}" class="btn-report-pill">
                            <i class="bi bi-table text-success"></i> Reporte de Mora y Cartera
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
