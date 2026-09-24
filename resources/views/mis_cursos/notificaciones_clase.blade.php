@extends('layouts.app')

@section('title', 'Notificaciones de Clase')

@section('styles')
    <style>
        .notifications-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            padding: 2.5rem;
            color: #f8fafc;
            margin-bottom: 2rem;
        }

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5) !important;
            border: 2px solid var(--border-dark) !important;
            color: #f8fafc !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
        }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--primary) !important;
            outline: none !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 178, 48, 0.2) !important;
        }

        .weekly-pill {
            background: rgba(30, 41, 59, 0.3);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
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
                <li class="breadcrumb-item active text-white" aria-current="page">Avisos y Recordatorios</li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="notifications-card">
            <div class="row align-items-center mb-4">
                <div class="col-md-9">
                    <h1 class="h2 fw-bold text-white mb-1"><i class="bi bi-megaphone text-danger me-2"></i> Avisos de Clase</h1>
                    <p class="text-white-50 mb-0">Redacte y envíe recordatorios de clase a todos los alumnos matriculados.</p>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="btn btn-outline-light rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="weekly-pill">
                        <h6 class="fw-bold text-white mb-3"><i class="bi bi-calendar-range me-2 text-primary"></i>1. Seleccionar Semana de Clase</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Semana del Cronograma</label>
                            <select id="selSemana" class="form-select form-select-custom w-100" onchange="autoCompletarMensaje(this)">
                                <option value="">-- Personalizado --</option>
                                @foreach ($cronograma as $cro)
                                    <option value="{{ $cro->semana }}" 
                                            data-fecha="{{ \Carbon\Carbon::parse($cro->fecha)->format('d/m/Y') }}" 
                                            data-actividad="{{ $cro->actividad }}" 
                                            data-tareas="{{ $cro->tareas ?? 'Sin tareas asignadas' }}">
                                        Semana {{ $cro->semana }} ({{ \Carbon\Carbon::parse($cro->fecha)->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <form action="{{ route('mis_cursos.notificaciones_clase.enviar', $curso->id_curso_activo) }}" method="POST" class="weekly-pill">
                        @csrf
                        <h6 class="fw-bold text-white mb-3"><i class="bi bi-envelope-open me-2 text-danger"></i>2. Redactar Aviso de Clase</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Asunto del Correo</label>
                            <input type="text" name="asunto" id="txtAsunto" class="form-control form-control-custom w-100" value="Recordatorio de clase: {{ $curso->planEstudio->materia }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-white-50">Cuerpo del Mensaje</label>
                            <textarea name="mensaje" id="txtMensaje" class="form-control form-control-custom w-100" rows="8" placeholder="Estimados estudiantes..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2.5 fw-bold"><i class="bi bi-send-fill me-2"></i> DESPACHAR NOTIFICACIÓN POR CORREO</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const materia = "{{ $curso->planEstudio->materia }}";
        const zoomLink = "{{ $curso->enlace_zoom ?: 'No configurado' }}";
        const horaInicio = "{{ $curso->hora_inicio ? \Carbon\Carbon::parse($curso->hora_inicio)->format('g:i a') : 'No configurada' }}";

        function autoCompletarMensaje(el) {
            const opt = el.options[el.selectedIndex];
            if (el.value === "") {
                document.getElementById('txtAsunto').value = `Aviso importante: ${materia}`;
                document.getElementById('txtMensaje').value = "";
                return;
            }

            const fecha = opt.dataset.fecha;
            const actividad = opt.dataset.actividad || 'Sesión de clase ordinaria';
            const tareas = opt.dataset.tareas || 'Ninguna';

            document.getElementById('txtAsunto').value = `Recordatorio de clase: ${materia} - Semana ${el.value}`;
            
            const msg = `Estimados estudiantes de ${materia},

Espero que se encuentren muy bien.

Les recuerdo que la sesión de la Semana ${el.value} está programada para el día ${fecha} a las ${horaInicio}.

Detalles de la sesión:
- Temas a desarrollar: ${actividad}
- Tareas/Actividades previas: ${tareas}

Enlace de acceso a la clase virtual en Zoom:
${zoomLink}

Favor conectarse puntualmente. ¡Nos vemos en clase!

Atentamente,
El Profesor`;
            
            document.getElementById('txtMensaje').value = msg;
        }

        // Initialize default
        $(document).ready(function() {
            autoCompletarMensaje(document.getElementById('selSemana'));
        });
    </script>
@endsection
