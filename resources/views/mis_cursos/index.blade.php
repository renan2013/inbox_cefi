@extends('layouts.app')

@section('title', 'Inbox BPM - Mis Cursos')

@section('styles')
    <style>
        .page-header {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .form-select-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            color: var(--text-light);
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .form-select-custom:focus {
            background-color: var(--card-dark);
            border-color: var(--primary);
            color: var(--text-light);
            outline: none;
        }

        /* Course Item Card */
        .course-item-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .course-item-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(95, 178, 48, 0.15);
        }

        .course-program-title {
            color: var(--primary) !important;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .course-materia-title {
            color: var(--text-light) !important;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .course-details-text {
            color: var(--text-muted) !important;
            font-size: 0.875rem;
        }

        .badge-code {
            background-color: rgba(100, 116, 139, 0.2) !important;
            color: var(--text-light) !important;
            border: 1px solid var(--border-dark) !important;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.7rem;
        }

        .badge-checklist {
            font-size: 0.65rem;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-checklist-active {
            background-color: rgba(95, 178, 48, 0.15) !important;
            color: #5fb230 !important;
            border: 1px solid rgba(95, 178, 48, 0.3) !important;
        }

        .badge-checklist-inactive {
            background-color: rgba(100, 116, 139, 0.1) !important;
            color: var(--text-muted) !important;
            border: 1px solid var(--border-dark) !important;
        }

        [data-theme="light"] .badge-checklist-active {
            background-color: rgba(95, 178, 48, 0.15) !important;
            color: #2e6b17 !important;
            border: 1px solid rgba(95, 178, 48, 0.4) !important;
        }

        [data-theme="light"] .badge-checklist-inactive {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #cbd5e1 !important;
        }

        .btn-course-panel {
            background-color: var(--primary) !important;
            color: #ffffff !important;
            border: none !important;
            padding: 0.65rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(95, 178, 48, 0.25);
            transition: all 0.2s ease;
        }

        .btn-course-panel:hover {
            background-color: var(--primary-dark) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(95, 178, 48, 0.35);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Mis Cursos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-end flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 text-white">
                    {{ $es_admin ? 'Todos los Cursos Programados' : 'Mis Cursos Asignados' }}
                </h1>
                <p class="text-white-50 mb-0">Gestión docente, control de rúbricas y entrega de actas oficiales.</p>
            </div>
            
            <div>
                <form action="{{ route('mis_cursos.index') }}" method="GET">
                    <select name="periodo" class="form-select form-select-custom" onchange="this.form.submit()">
                        @foreach ($periodos as $p)
                            <option value="{{ $p }}" {{ $p === $periodo_actual ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- LISTADO DE CURSOS -->
        <div class="row g-4">
            @if (count($cursos) > 0)
                @foreach ($cursos as $c)
                    <div class="col-lg-6">
                        <div class="course-item-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="pe-2">
                                    <span class="course-program-title d-block mb-1">
                                        {{ $c->planEstudio->programa->nombre_programa ?? 'Programa no definido' }}
                                    </span>
                                    <h4 class="course-materia-title mb-0">{{ $c->planEstudio->materia }}</h4>
                                </div>
                                <span class="badge badge-code flex-shrink-0">
                                    {{ $c->planEstudio->codigo }}
                                </span>
                            </div>

                            <p class="course-details-text mb-3">
                                <i class="bi bi-clock me-1"></i> Horario: <strong>{{ $c->horario ?: 'Sin horario asignado' }}</strong><br>
                                <i class="bi bi-person-fill me-1"></i> Docente: <strong>{{ $c->profesor->nombre ?? 'N/A' }} {{ $c->profesor->apellidos ?? '' }}</strong>
                            </p>

                            <!-- Checklist metrics badges -->
                            <div class="d-flex flex-wrap gap-1.5 mb-4">
                                <span class="badge badge-checklist {{ $c->check_recursos ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Recursos</span>
                                <span class="badge badge-checklist {{ $c->check_portada ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Portada</span>
                                <span class="badge badge-checklist {{ $c->check_moodle ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Moodle</span>
                                <span class="badge badge-checklist {{ $c->check_estudiantes ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Alumnos</span>
                                <span class="badge badge-checklist {{ $c->check_actividades ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Act.</span>
                                <span class="badge badge-checklist {{ $c->check_calificaciones ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Notas</span>
                                <span class="badge badge-checklist {{ $c->check_acta ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Acta</span>
                                <span class="badge badge-checklist {{ $c->check_pago ? 'badge-checklist-active' : 'badge-checklist-inactive' }}">Pago</span>
                            </div>

                            <div class="d-grid">
                                <a href="{{ route('mis_cursos.ver', $c->id_curso_activo) }}" class="btn btn-course-panel text-center">
                                    <i class="bi bi-gear-wide-connected me-1"></i> Abrir Panel de Curso
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="card glass-card p-5 text-center text-white-50">
                        <i class="bi bi-book display-4 d-block mb-3"></i>
                        No se encontraron cursos programados o asignados para el periodo seleccionado.
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
