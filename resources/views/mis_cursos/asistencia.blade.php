@extends('layouts.app')

@section('title', 'Inbox - Asistencia del Curso')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .weeks-scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 0.5rem;
            padding-bottom: 1rem;
        }
        .week-btn {
            min-width: 100px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border-dark);
            color: #94a3b8;
            border-radius: 2rem;
            padding: 0.5rem 1rem;
            text-align: center;
            font-weight: bold;
            transition: all 0.2s;
            cursor: pointer;
        }
        .week-btn:hover, .week-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(95, 178, 48, 0.3);
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #cbd5e1 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        /* Attendance status pills */
        .status-radio {
            display: none;
        }
        .status-label {
            cursor: pointer;
            border-radius: 2rem;
            padding: 0.35rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid var(--border-dark);
            background: rgba(30, 41, 59, 0.2);
            color: #94a3b8;
            transition: all 0.2s;
            text-transform: uppercase;
        }

        .status-radio:checked + .status-label.lbl-presente {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: #10b981;
        }
        .status-radio:checked + .status-label.lbl-ausente {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: #ef4444;
        }
        .status-radio:checked + .status-label.lbl-tarde {
            background-color: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: #f59e0b;
        }
        .status-radio:checked + .status-label.lbl-justificado {
            background-color: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: #3b82f6;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mis_cursos.index') }}" class="text-decoration-none text-white-50">Mis Cursos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="text-decoration-none text-white-50">{{ $curso->planEstudio->materia }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Registro de Asistencia</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Control de Asistencia</h1>
                <p class="text-white-50 mb-0">Seleccione la clase del cronograma y marque la asistencia de los alumnos.</p>
            </div>
            <div>
                <a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Curso
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- WEEKS HORIZONTAL SELECTOR -->
        <div class="weeks-scroll-container mb-4">
            @foreach ($cronograma as $crono)
                <a href="{{ route('mis_cursos.asistencia', [$curso->id_curso_activo, 'semana' => $crono['semana']]) }}" 
                   class="week-btn text-decoration-none {{ $semana_activa == $crono['semana'] ? 'active' : '' }}">
                    Semana {{ $crono['semana'] }}
                    <span class="d-block x-small font-weight-normal text-white-50">{{ \Carbon\Carbon::parse($crono['fecha'])->format('d/m/Y') }}</span>
                </a>
            @endforeach
        </div>

        <!-- DETAILS OF SELECTED SESSION -->
        <div class="card glass-card p-4 text-white mb-4">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h5 class="fw-bold text-success mb-1" style="color: var(--primary) !important;"><i class="bi bi-calendar-event me-2"></i> Semana {{ $semana_activa }}</h5>
                    <p class="mb-0 text-white-50 small">
                        <strong>Fecha de la clase:</strong> {{ \Carbon\Carbon::parse($fecha_clase_activa)->format('d/m/Y') }} <br>
                        <strong>Actividad Planificada:</strong> {{ $actividad_activa }}
                    </p>
                </div>
                <div class="col-md-3 text-md-end mt-3 mt-md-0">
                    <button type="submit" form="asistenciaForm" class="btn btn-primary rounded-pill px-4" style="background-color: var(--primary); border: none;">
                        <i class="bi bi-save me-1"></i> Guardar Asistencia
                    </button>
                </div>
            </div>
        </div>

        <!-- STUDENTS ATTENDANCE GRID -->
        <div class="card glass-card">
            <form action="{{ route('mis_cursos.asistencia.store', $curso->id_curso_activo) }}" method="POST" id="asistenciaForm">
                @csrf
                <input type="hidden" name="semana_activa" value="{{ $semana_activa }}">
                <input type="hidden" name="fecha_clase_activa" value="{{ $fecha_clase_activa }}">
                
                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle">
                        <thead>
                            <tr class="bg-dark text-white-50">
                                <th class="ps-4">Estudiante</th>
                                <th class="text-center" style="width: 50%;">Estado de Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($estudiantes) > 0)
                                @foreach ($estudiantes as $est)
                                    @php
                                        $estado_guardado = $asistencias_cargadas[$est->id_estudiante] ?? 'presente';
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <strong class="text-white d-block small">{{ $est->nombre }} {{ $est->apellidos }}</strong>
                                            <span class="text-white-50 x-small"><i class="bi bi-card-text me-1"></i>{{ $est->cedula ?: 'Sin cédula' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- Presente -->
                                                <input type="radio" name="asistencias[{{ $est->id_estudiante }}]" value="presente" id="pres_{{ $est->id_estudiante }}" class="status-radio" {{ $estado_guardado === 'presente' ? 'checked' : '' }}>
                                                <label for="pres_{{ $est->id_estudiante }}" class="status-label lbl-presente">Presente</label>

                                                <!-- Ausente -->
                                                <input type="radio" name="asistencias[{{ $est->id_estudiante }}]" value="ausente" id="aus_{{ $est->id_estudiante }}" class="status-radio" {{ $estado_guardado === 'ausente' ? 'checked' : '' }}>
                                                <label for="aus_{{ $est->id_estudiante }}" class="status-label lbl-ausente">Ausente</label>

                                                <!-- Tarde -->
                                                <input type="radio" name="asistencias[{{ $est->id_estudiante }}]" value="tarde" id="tar_{{ $est->id_estudiante }}" class="status-radio" {{ $estado_guardado === 'tarde' ? 'checked' : '' }}>
                                                <label for="tar_{{ $est->id_estudiante }}" class="status-label lbl-tarde">Tarde</label>

                                                <!-- Justificado -->
                                                <input type="radio" name="asistencias[{{ $est->id_estudiante }}]" value="justificado" id="jus_{{ $est->id_estudiante }}" class="status-radio" {{ $estado_guardado === 'justificado' ? 'checked' : '' }}>
                                                <label for="jus_{{ $est->id_estudiante }}" class="status-label lbl-justificado">Justificado</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-white-50">No hay estudiantes matriculados en este curso.</td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>
@endsection
